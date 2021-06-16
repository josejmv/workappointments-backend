<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\User;
use App\Entity\Admin;
use App\Entity\Manager;
use App\Entity\Operator;
use App\Mailer\Mailer;

class AuthController extends AbstractController
{

    public function __construct(
        JWTEncoderInterface $jwtEncoder,
        JWSProviderInterface $jwsProvider
        ) {
        $this->jwtEncoder = $jwtEncoder;
        $this->jwsProvider = $jwsProvider;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/login", name="login", methods="POST")
     */
    public function login(Request $request){
        $data = @json_decode($request->getContent(),true);
        try {
            if(is_array($data) && array_key_exists("password",$data) && array_key_exists("email",$data)){
                $hashedPassword = hash("sha256",$data['password']);
                $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([ "email" => $data["email"], "password" => $hashedPassword]);
                if($user != null){
                    switch ($user->getRol()) {
                        case 'Admin': {
                            $obj = $this->getDoctrine()->getRepository(Admin::class)->findOneBy([ "adminData" => $user ]);
                        }break;
                        case 'Manager':{
                            $obj = $this->getDoctrine()->getRepository(Manager::class)->findOneBy([ "managerData" => $user ]);
                        }break;
                        case 'Operator':{
                            $obj = $this->getDoctrine()->getRepository(Operator::class)->findOneBy([ "operatorData" => $user ]);
                        }break;
                        default: $obj = null;
                    }
                    if($obj != null){
                        $pass = true;
                        if($user->getRol() == "Manager" || $user->getRol() == "Operator")
                            if(!$obj->getActive()) $pass = false;
                        if($pass){
                            $token = $this->jwtEncoder->encode([
                                'id' => $user->getId(),
                                'rol' => $user->getRol(),
                                'exp' => time() + (3600 * 6),
                                'verified'=>true,
                            ]);
                            $this->jwsProvider->create(["token" => $token], [$token]);
                            $response = array(
                                'code'=>1,
                                'message'=>'Success',
                                'error'=>null,
                                'results' => array(
                                    "token" => $token,
                                    "rol" => $user->getRol(),
                                )
                            );
                        } else{
                            $response = array(
                                'code'=>0,
                                'message'=>'Error',
                                'error'=>"No esta permitido su acceso",
                                'results' => null
                            );
                        }
                    } else
                        $response = array(
                            'code'=>0,
                            'message'=>'Error',
                            'error'=>"Rol no definido",
                            'results' => null
                        );
                } else{
                    $response = array(
                        'code'=>0,
                        'message'=>'Error',
                        'error'=>"Usuario o contraseña invalidos",
                        'results' => null
                    );
                }
            } else{
                $response = array(
                    'code'=>0,
                    'message'=>'Error',
                    'error'=>"faltan campos email o password",
                    'results' => null
                );
            }
        } catch (\Throwable $th) {
            $response = array(
                'code'=>0,
                'message'=>'Error',
                'error'=>$th->getMessage(),
                'results'=>null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/logup", name="logup", methods="POST")
     */
    public function logup(Request $request){
        $data = @json_decode($request->getContent(),true);
        try {
            if(is_array($data) && array_key_exists("password",$data)){
                $hashedPassword = hash("sha256", $data["password"]);
                $user = new User();
                if(array_key_exists("email",$data)){
                    $user->setEmail($data['email']);
                    $user->setPassword($hashedPassword);
                    $user->setActive(true);
                    if(array_key_exists("userInfo",$data)){
                        $userInfo = new UserInfo();
                        $userInfo->setFirstName($data['userInfo']['firstName']);
                        $userInfo->setLastName($data['userInfo']['lastName']);
                        $user->setUserInfo($userInfo);
                    }
                    if(array_key_exists("rol",$data)){
                        $user->setRol($data['rol']);
                        $entity = null;
                        if($data['rol'] == "Admin"){
                            $entity = new Admin();
                            $entity->setAdminData($user);
                        }
                        if($data['rol'] == "Manager"){
                            $entity = new Manager();
                            $entity->setManagerData($user);
                        }
                        if($data['rol'] == "Operator"){
                            $entity = new Operator();
                            $entity->setOperatorData($user);
                        }
                        if($entity != null){
                            $newUser = $this->getDoctrine()->getManager();
                            $newUser->persist($entity);
                            $newUser->flush();
                            $response = array(
                                'code'=>1,
                                'message'=>'success',
                                'error'=>null,
                                'results'=>"Usuario creado con exito"
                            );
                        } else{
                            $response = array(
                                'code'=>0,
                                'message'=>'Error',
                                'error'=>"Este rol no existe",
                                'results'=>null
                            );
                        }
                    } else{
                        $response = array(
                            'code'=>0,
                            'message'=>'Error',
                            'error'=>"Ingrese campo rol",
                            'results'=>null
                        );
                    }
                } else{
                    $response = array(
                        'code'=>0,
                        'message'=>'Error',
                        'error'=>"Ingrese campo email",
                        'results'=>null
                    );
                }
            } else{
                $response = array(
                    'code'=>0,
                    'message'=>'Error',
                    'error'=>"Ingrese campo password",
                    'results'=>null
                );
            }
        } catch (\Throwable $th) {
            $response = array(
                'code'=>0,
                'message'=>'Error',
                'error'=>$th->getMessage(),
                'results'=>null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @Route("/restore-password", name="restorePassword", methods="POST")
     */
    public function restorePassword(Request $request, Mailer $mailer)
    {
        $data = @json_decode($request->getContent(),true);
        if(is_array($data) && array_key_exists("email",$data)){
            $setting = 'restore_password';
            $token = $this->jwtEncoder->encode([
                'email' => $data['email'],
                'exp' => time() + 3600,
                'verified'=>true,
            ]);
            $confirm = "https://workappointments.vercel.app/login/$token";
            try {
                $response = $mailer->sendRestorePasswordEmail($setting, 'josejmvasquez@gmail.com', $confirm);
                $response = array(
                    "code" => 1,
                    "message" => "success",
                    "error" => null,
                    "results" => $response
                );
            } catch (\Throwable $th) {
                $response = array(
                    "code" => 0,
                    "message" => "error",
                    "error" => $th->getMessage(),
                    "results" => null
                );
            }
        } else{
            $response = array(
                "code" => 0,
                "message" => "error",
                "error" => "Ingrese un email",
                "results" => null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }
}
