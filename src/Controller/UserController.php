<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use App\Entity\UserInfo;
use App\Entity\UserInterests;

class UserController extends AbstractController
{
    public function __construct(JWSProviderInterface $jwsProvider) {
        $this->jwsProvider = $jwsProvider;
    }

    /**
     * @param $id
     * @return JsonResponse
     * @Route("/user/{id}", name="getUserData", methods="GET")
     */
    public function getUserData($id){
        try{
            $user = $this->getDoctrine()->getRepository(User::class)->find($id);
            $userData = @json_decode($this->get('serializer')->serialize($user,'json',[
                'circular_reference_handler' => function ($object) {
                            return $object->getId();
                }
            ]),true);
            unset($userData['password']);
            $response = array(
                'code'=>1,
                'message'=>"Success",
                'error'=>null,
                'results'=>$userData
            );
        } catch(\Throwable $th){
            $response = array(
                'code'=>0,
                'message'=>"Error",
                'error'=>$th->getMessage(),
                'results'=>null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @param $page
     * @param $active
     * @return JsonResponse
     * @Route("/paginator/users", name="paginatorUsers", methods="GET")
     */
    public function paginatorUsers(){
        try{
            $userLimitPage = count($this->getDoctrine()->getRepository(User::class)->findAll());
            $userLimitPage = round($userLimitPage / 20);
            if($userLimitPage < 1) $userLimitPage = 1;
            $response = array(
                'code'=>1,
                'message'=>'Success',
                'error'=>null,
                'results'=>$userLimitPage
            );
        } catch(\Throwable $th){
            $response = array(
                'code'=>0,
                'message'=>'Ha ocurrido un error',
                'error'=>$th->getMessage(),
                'results'=>null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @param $page
     * @param $active
     * @return JsonResponse
     * @Route("/users/{page}&{active}", name="getAllUsers", methods="GET")
     */
    public function getAllUsers($page, $active){
        try{
            $users = $this->getDoctrine()->getRepository(User::class)->findBy(["active" => $active === "true" ? true : false], null, 20, ($page - 1 ) * 20);
            $managers = [];
            foreach ($users as $user)
                if($user->getRol() !== 'Operator') $managers[] = $user;
            $usersData = @json_decode($this->get('serializer')->serialize($managers,'json',[
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]),true);
            for ($i=0; $i < count($usersData); $i++) unset($usersData[$i]['password']);
            $response = array(
                'code'=>1,
                'message'=>"Success",
                'error'=>null,
                'results'=>$usersData
            );
        } catch(\Throwable $th){
            $response = array(
                'code'=>0,
                'message'=>"Error",
                'error'=>$th->getMessage(),
                'results'=>null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/user", name="updateUser", methods="PUT")
     */
    public function updateUser(Request $request){
        $data = @json_decode($request->getContent(),true);
        $token = $this->checkToken($request->headers->get('token'));
        try {
            if($token['active']){
                $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                if(is_array($data)){
                    if(array_key_exists('secondaryEmail', $data)){
                        $mainEmail = $this->getDoctrine()->getRepository(User::class)->findOneBy(["email" => $data['secondaryEmail']]);
                        if(!$mainEmail){
                            $user->setSecondaryEmail($data['secondaryEmail']);
                        } else{
                            return new JsonResponse(array(
                                "code" => 0,
                                "message" => "Error",
                                "error" => "Email ya en uso",
                                "results" => null
                            ), Response::HTTP_CREATED);
                        }
                    }
                    if(array_key_exists('userInfo', $data)){
                        $userInfoData = $this->get('serializer')->deserialize(@json_encode($data['userInfo']),UserInfo::class,'json');
                        $exist = true;
                        $userInfo = $user->getUserInfo();
                        if(!$userInfo){
                            $exist = false;
                            $userInfo = new UserInfo();
                        }
                        $userInfo->setFirstName($userInfoData->getFirstName());
                        $userInfo->setLastName($userInfoData->getLastName());
                        $userInfo->setBirthday($userInfoData->getBirthday());
                        $userInfo->setPhone($userInfoData->getPhone());
                        $userInfo->setCountry($userInfoData->getCountry());
                        $userInfo->setCity($userInfoData->getCity());
                        $userInfo->setProvince($userInfoData->getProvince());
                        $userInfo->setResidence($userInfoData->getResidence());
                        if(!$exist)
                            $user->setUserInfo($userInfo);
                    }
                    if(array_key_exists("userInterests",$data)){
                        $userInterestsData = $this->get('serializer')->deserialize(@json_encode($data['userInterests']),UserInterests::class,'json');
                        $exist = true;
                        $userInterests = $user->getUserInterests();
                        if(!$userInterests){
                            $exist = false;
                            $userInterests = new UserInterests();
                        }
                        $userInterests->setPositions($userInterestsData->getPositions());
                        $userInterests->setDriveCarnet($userInterestsData->getDriveCarnet());
                        $userInterests->setHasCar($userInterestsData->getHasCar());
                        $userInterests->setHasMotorcycle($userInterestsData->getHasMotorcycle());
                        $userInterests->setSchedule($userInterestsData->getSchedule());
                        $userInterests->setWorkTipe($userInterestsData->getWorkTipe());
                        $userInterests->setServiceWorker($userInterestsData->getServiceWorker());
                        $userInterests->setDisability($userInterestsData->getDisability());
                        $userInterests->setDisabilityLevel($userInterestsData->getDisabilityLevel());
                        $userInterests->setIncorporate($userInterestsData->getIncorporate());
                        if(!$exist)
                            $user->setUserInterests($userInterests);
                    }
                    $update = $this->getDoctrine()->getManager();
                    $update->persist($user);
                    $update->flush();
                    $code = 1;
                    $message = "Success";
                    $error = null;
                    $result = null;
                } else{
                    $code = 0;
                    $message = "Error";
                    $error = "Ingrese datos";
                    $result = null;
                }
            } else {
                $code = -1;
                $message = "Error";
                $error = "No access";
                $result = null;
            }
        } catch (\Throwable $th) {
            $code = 0;
            $message = "Error";
            $error = $th->getMessage();
            $result = null;
        }
        $response = array(
            'code'=>$code,
            'message'=>$message,
            'error'=>$error,
            'results'=>$result
        );
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @Route("/user/{id}", name="deleteUser", methods="DELETE")
     */
    public function deleteUser(Request $request, $id){
        $token = $this->checkToken($request->headers->get('token'));
        try {
            if($token['active']){
                $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                $rol = $user->getRol();
                if ($rol === "Admin" || $rol === "Manager"){
                    $userToDelete = $this->getDoctrine()->getRepository(User::class)->find($id);
                    $havePermissions = false;
                    if($userToDelete->getRol() === "Manager" && $rol === "Admin")
                    $havePermissions = true;
                    if($userToDelete->getRol() === "Operator")
                        $havePermissions = true;
                    if($havePermissions){
                        $userToDelete->setActive(!$userToDelete->getActive());
                        $delete = $this->getDoctrine()->getManager();
                        $delete->persist($userToDelete);
                        $delete->flush();
                        $code = 1;
                        $message = "Success";
                        $error = null;
                    } else{
                        $code = 0;
                        $message = "Error";
                        $error = "no tiene permisos para deshabilitar este usuario";
                    }
                } else{
                    $code = 0;
                    $message = "Error";
                    $error = "No tiene permisos suficiente para realizar esta operacion";
                }
            } else{
                $code = -1;
                $message = "Error";
                $error = "No access";
            }
        } catch (\Throwable $th) {
            $code = 0;
            $message = "Error";
            $error = $th->getMessage();
        }
        $response = array(
            'code'=>$code,
            'message'=>$message,
            'error'=>$error,
            'results'=>null
        );
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/update-password/", name="updatePassword", methods="PUT")
     */
    public function updatePassword(Request $request){
        $token = $this->checkToken($request->headers->get('token'));
        $data = @json_decode($request->getContent(),true);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["email" => $token['data']['email']]);
        if($user instanceof User){
            if(is_array($data) && array_key_exists('password', $data) && array_key_exists('newPassword', $data)){
                if($data['password'] == $data['newPassword']){
                    $hashedPassword = hash("sha256", $data["password"]);
                    $user->setPassword($hashedPassword);
                    $updatePassword = $this->getDoctrine()->getManager();
                    $updatePassword->persist($user);
                    $updatePassword->flush();
                    $response = array(
                        'code'=> 1,
                        'message'=> 'Contraseña cambiada con exito',
                        'error'=> null,
                        'results'=>null
                    );   
                } else{
                    $response = array(
                        'code'=> 0,
                        'message'=> 'Error',
                        'error'=> 'Las contraseñas no coinciden',
                        'results'=>null
                    );    
                }
            } else{
                $response = array(
                    'code'=> 0,
                    'message'=> 'Error',
                    'error'=> 'Ingrese password o newPassword',
                    'results'=>null
                );    
            }
        } else{
            $response = array(
                'code'=> 0,
                'message'=> 'Error',
                'error'=> 'Usuario no encontrado',
                'results'=>null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    public function checkToken($token){
        if($token){
            $result = $this->get('serializer')->serialize($this->jwsProvider->load($token),'json',[]);
            $result = @json_decode($result,true);
            return array(
                'data'=> $result['payload'],
                'active' => !$result['expired']
            );
        } else{
            return array(
                'data'=> null,
                'active' => false
            );
        }
    }
}
