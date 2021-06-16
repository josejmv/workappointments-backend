<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\AppointmentResume;
use App\Entity\UserInterests;
use App\Entity\ServiceResume;
use App\Entity\Appointment;
use App\Entity\Operator;
use App\Entity\Service;
use App\Mailer\Mailer;
use App\Entity\User;

class SendEmailController extends AbstractController
{
    public function __construct(JWTEncoderInterface $jwtEncoder){
        $this->jwtEncoder = $jwtEncoder;
    }
    
// START RESTORE EMAIL PROCESS

    /**
     * @param Request $request
     * @Route("/mailer/restore-password/", name="restorePassword", methods="POST")
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

// END RESTORE EMAIL PROCESS
// START SERVICE EMAIL PROCESS

    /**
     * @param $token
     * @Route("/mailer/service/{token}/", name="serviceMail", methods="PUT")
     */
    public function serviceMail($token, Mailer $mailer){
        $service = $this->getDoctrine()->getRepository(Service::class)->findOneBy([ "newCreated" => $token ]);
        if($service instanceof Service){
            $serviceId = $service->getId();
            $filter = @json_decode($service->getFilter(),true);
            $sends = 0;
            $response = null;
            $interests = [];
            if(is_array($filter)){
                try{
                    foreach($filter as $key => $attribute){
                        $found = $this->searchServiceOperators($key,$attribute);
                        if($found != null){
                            foreach ($found as $interest){
                                if(!in_array($interest, $interests))
                                    $interests[] = $interest;
                            }
                        }
                    }
                    foreach ($interests as $interest) {
                        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([ "userInterests" => $interest ]);
                        $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy([ "operatorData" => $user ]);
                        if(!$operator instanceof Operator) continue;
                        $resume = new ServiceResume();
                        $resume->setService($service);
                        $resume->setOperator($operator);
                        $resume->setServiceDate($service->getDate());
                        $saveResume = $this->getDoctrine()->getManager();
                        $saveResume->persist($resume);
                        $saveResume->flush();
                        if($user->getUserInfo() instanceof UserInfo)
                            $name = $user->getUserInfo()->getFirstName();
                        else $name = $user->getEmail();
                        $email = "josejmvasquez@gmail.com";
                        $this->sendServices($name, $email, $serviceId, $mailer);
                        $sends++;
                    }
                    $response = array(
                        'code'=>1,
                        'message'=>"Success",
                        'error'=>null,
                        'results'=> $sends
                    );
                } catch(\Throwable $th){
                    $response = array(
                        'code'=>0,
                        'message'=>"Error",
                        'error'=>$th->getMessage(),
                        'results'=>null
                    );
                }
            } else{
                $response = array(
                    'code'=>0,
                    'message'=>"Error",
                    'error'=>"Seleccione el tipo de servicio",
                    'results'=>null
                );
            }
        } else $response = array(
                'code'=>0,
                'message'=>"Error",
                'error'=>"Servicio no encontrado",
                'results'=>null
            );
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

// END SERVICE EMAIL PROCESS
// START APPOINTMENTS EMAIL PROCESS

    /**
     * @param $token
     * @Route("/mailer/appointment/{token}/", name="appointmentMail", methods="PUT")
     */
    public function appointmentMail($token, Mailer $mailer){
        $appointment = $this->getDoctrine()->getRepository(Appointment::class)->findOneBy([ "newCreated" => $token ]);
        if($appointment instanceof Appointment){
            $appointmentId = $appointment->getId();
            $workType = $appointment->getTipeWork();
            $sends = 0;
            if(is_array($workType)){
                try{
                    $interests = $this->searchAppointmentOperators($workType);
                    foreach ($interests as $interest) {
                        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([ "userInterests" => $interest ]);
                        $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy([ "operatorData" => $user ]);
                        if($operator instanceof Operator){
                            try{
                                $resume = new AppointmentResume();
                                $resume->setAppointment($appointment);
                                $resume->setOperator($operator);
                                // $saveResume = $this->getDoctrine()->getManager();
                                // $saveResume->persist($resume);
                                // $saveResume->flush();
                                if($user->getUserInfo()) $name = $user->getUserInfo()->getFirstName();
                                else $name = $user->getEmail();
                                $email = "josejmvasquez@gmail.com";
                                $this->sendAppointments($name, $email, $appointmentId, $mailer);
                                $sends++;
                            }catch(\Throwable $error){
                                $response = array(
                                    'code'=>0,
                                    'message'=>"Error",
                                    'error'=>$error->getMessage(),
                                    'results'=>null
                                );
                            }
                        }   
                    }
                    $response = array(
                        'code'=>1,
                        'message'=>"Success",
                        'error'=>null,
                        'results'=>$sends
                    );
                } catch(\Throwable $th){
                    $response = array(
                        'code'=>0,
                        'message'=>"Error",
                        'error'=>$th->getMessage(),
                        'results'=>null
                    );
                }
            } else{
                $response = array(
                    'code'=>0,
                    'message'=>"Error",
                    'error'=>"workType debe ser un array",
                    'results'=>null
                );
            }
        } else $response = array(
                'code'=>0,
                'message'=>"Error",
                'error'=>"No se ha encontrado la cita",
                'results'=>null
            );
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

// END APPOINTMENTS EMAIL PROCESS

    public function sendServices($name, $email, $serviceId, Mailer $mailer)
    {
        $setting = "generate_service";
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["email" => $email]);
        $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
        $service = $this->getDoctrine()->getRepository(Service::class)->findOneBy(["id" => $serviceId]);
        $title = $service->getTitle();
        $description = $service->getDescription();
        $token = $this->jwtEncoder->encode([
            'type' => "Service",
            'exp' => time() + 3600,
            'verified'=>true,
        ]);
        $confirm = "https://workappointments.vercel.app/servicios/$serviceId/$token";
        try {
            $response = $mailer->sendServiceEmail($setting, $email, $confirm, $name, $title, $description);
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
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    public function sendAppointments($name, $email, $appointmentId, Mailer $mailer)
    {
        $setting = "generate_appointment";
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["email" => $email]);
        $password = explode("@",$email)[0];
        $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
        $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($appointmentId);
        $title = $appointment->getTitle();
        $description = $appointment->getDescription();
        $token = $this->jwtEncoder->encode([
            'type' => "Appointment",
            'exp' => time() + 3600,
            'verified'=>true,
        ]);
        $confirm = "https://workappointments.vercel.app/citas/$appointmentId/$token";
        try {
            $response = $mailer->sendAppointmentEmail($name, $email, $password, $setting, $confirm, $title, $description);
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
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    public function searchServiceOperators($key, $attribute){
        $found = $this->getDoctrine()->getRepository(UserInterests::class)->getByFilter($key, $attribute);
        return $found;
    }

    public function searchAppointmentOperators($attribute){
        $found = $this->getDoctrine()->getRepository(UserInterests::class)->getByPosition($attribute);
        return $found;
    }
}
