<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Admin;
use App\Entity\Manager;
use App\Entity\Operator;
use App\Entity\Appointment;
use App\Entity\AppointmentResume;
use App\Entity\User;
use App\Entity\UserInfo;

class AppointmentsController extends AbstractController
{
    public function __construct(JWTEncoderInterface $jwtEncoder, JWSProviderInterface $jwsProvider){
        $this->jwtEncoder = $jwtEncoder;
        $this->jwsProvider = $jwsProvider;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/appointment/", name="addAppointment", methods="POST")
     */
    public function addAppointment(Request $request){
        $data = @json_decode($request->getContent(),true);
        $token = $this->checkToken($request->headers->get('token'));
        try {
            if($token['active']){
                $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                if(array_key_exists("appointment", $data)){
                    $appointmentData = json_encode($data["appointment"]);
                    $entity = null;
                    if($user->getRol() === "Admin")
                        $entity = $this->getDoctrine()->getRepository(Admin::class)->findOneBy(["adminData" => $user]);
                    if($user->getRol() === "Manager")
                        $entity = $this->getDoctrine()->getRepository(Manager::class)->findOneBy(["managerData" => $user]);
                    $appointment = $this->get("serializer")->deserialize($appointmentData, Appointment::class,'json');
                    $appointment->setActive(true);
                    $quantityAppointments = count($this->getDoctrine()->getRepository(Appointment::class)->findAll());
                    $token = $this->jwtEncoder->encode([
                        'id'=>$quantityAppointments + 1,
                        'exp' => time() + 3600,
                        'verified'=>true,
                    ]);
                    $appointment->setNewCreated($token);
                    if($entity){
                        $entity->addAppointment($appointment);
                        try {
                            $newAppointment = $this->getDoctrine()->getManager();
                            $newAppointment->persist($entity);
                            $newAppointment->flush();
                            $response = array(
                                'code'=>1,
                                'message'=>'Success',
                                'error'=>null,
                                'results' => $token
                            );
                        } catch (\Throwable $th) {
                            $response = array(
                                'code'=>0,
                                'message'=>'Error',
                                'error'=>$th->getMessage(),
                                'results'=>null
                            );
                        }
                    } else $response = array(
                            'code'=>0,
                            'message'=>'Error',
                            'error'=>"No tiene permisos para crear una cita",
                            'results'=>null
                        );
                } else $response = array(
                        'code'=>0,
                        'message'=>'Error',
                        'error'=>"Falta campo appointment",
                        'results'=>null
                    );
            } else $response = array(
                    'code'=> -1,
                    'message'=> 'Error',
                    'error'=>"No access",
                    'results'=>null
                );
        } catch (\Throwable $th) {
            $response = array(
                'code'=>0,
                'message'=>'Error',
                'error'=>$th->getMessage(),
                'results'=>'Ha ocurrido un error'
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    /**
     * @param $page
     * @param $active
     * @return JsonResponse
     * @Route("/paginator/appointments/", name="paginatorAppointments", methods="GET")
     */
    public function paginatorAppointments(){
        try{
            $appointmentLimitPage = count($this->getDoctrine()->getRepository(Appointment::class)->findAll());
            $appointmentLimitPage = round($appointmentLimitPage / 20);
            if($appointmentLimitPage < 1) $appointmentLimitPage = 1;
            $response = array(
                'code'=>1,
                'message'=>'Success',
                'error'=>null,
                'results'=>$appointmentLimitPage
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
     * @Route("/appointments/{page}&{active}/", name="showAppointments", methods="GET")
     */
    public function showAppointments($page, $active){
        try{
            $appointments = $this->getDoctrine()->getRepository(Appointment::class)->findBy(["active" => $active === "true" ? true : false], null, 20, ($page - 1 ) * 20);
            if(!empty($appointments)){
                foreach ($appointments as $appointment){
                    $formatedAppointment = @json_decode($this->get('serializer')->serialize($appointment,'json',[
                        'circular_reference_handler' => function ($object) {
                            return $object->getId();
                        }
                    ]),true);
                    $formatedAppointment['available'] = $formatedAppointment['quantityEmployees'] - count($formatedAppointment['operators']);
                    $formatedAppointment['date'] = $appointment->getDate()->format('d-m-Y');
                    $formatedAppointment['hour'] = $appointment->getHour()->format('H:i');
                    unset($formatedAppointment['operators']);
                    unset($formatedAppointment['appointmentResume']);
                    unset($formatedAppointment['newCreated']);
                    $activeAppointments[] = $formatedAppointment;
                }
                $response = array(
                    'code'=>1,
                    'message'=>'success',
                    'error'=>null,
                    'results'=>$activeAppointments
                );
            } else {
                $response = array(
                    'code'=>1,
                    'message'=>'Success',
                    'error'=>null,
                    'results'=>[]
                );
            }
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
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @Route("/appointment/{id}/", name="singleAppointment", methods="GET")
     */
    public function singleAppointment(Request $request, $id){
        try{
            $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($id);
            $code = 1;
            $message = "success";
            $error = null;
            $result = @json_decode($this->get('serializer')->serialize($appointment,'json',[
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]), true);
            $result['available'] = $result['quantityEmployees'] - count($result['operators']);
            unset($result['operators']);
            unset($result['appointmentResume']);
            $response = array(
                "code" => $code,
                "message" => $message,
                "error" => $error,
                "results" => $result
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
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @Route("/appointment/{id}/", name="deleteAppointment", methods="DELETE")
     */
    public function deleteAppointment(Request $request, $id){
        $data = @json_decode($request->getContent(),true);
        $token = $this->checkToken($request->headers->get('token'));
        try{
            if($token['active']){
                $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                if($user->getRol() === "Manager" || $user->getRol() === "Admin"){
                    $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($id);
                    $appointment->setActive(!$appointment->getActive());
                    $delete = $this->getDoctrine()->getManager();
                    $delete->persist($appointment);
                    $delete->flush();
                    $code = 1;
                    $message = "success";
                    $error = null;
                    $result = null;
                } else {
                    $code = 0;
                    $message = "error";
                    $error = "Usted no tiene permisos suficientes para realizar esta operacion";
                    $result = null;
                }
            } else{
                $code = -1;
                $message = "Error";
                $error = "No access";
                $result = null;
            }
            $response = array(
                "code" => $code,
                "message" => $message,
                "error" => $error,
                "results" => $result
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
     * @param $title
     * @return JsonResponse
     * @Route("/search/appointments/{title}/", name="searchAppointments", methods="GET")
     */
    public function searchAppointments($title){
        try{
            $appointments = $this->getDoctrine()->getRepository(Appointment::class)->search($title);
            $appointmentsPerFilter = @json_decode($this->get('serializer')->serialize($appointments,'json',[
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]),true);
            $res = [];
            if(!empty($appointmentsPerFilter)){
                foreach($appointmentsPerFilter as $key => $appointment){
                    $appointment['available'] = $appointment['quantityEmployees'] - count($appointment['operators']);
                    $appointment['date'] = $appointments[$key]->getDate()->format('d-m-Y');
                    $appointment['hour'] = $appointments[$key]->getHour()->format('H:i');
                    unset($appointment['operators']);
                    unset($appointment['appointmentResume']);
                    unset($appointment['newCreated']);
                    $res[] = $appointment;
                }
            }
            $response = array(
                'code'=>1,
                'message'=>"Success",
                'error'=>null,
                'results'=>$res
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
    
    // /**
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @Route("/api/remove/appointment/operator/", name="removeOperatorToAppointment", methods={"POST"})
    //  */
    //  public function removeOperatorToAppointment(Request $request){
    //     $data = @json_decode($request->getContent(),true);
    //     try{
    //         if(is_array($data) && array_key_exists("appointmentId", $data)){
    //             if(array_key_exists("operatorId", $data)){
    //                 $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($data['appointmentId']);
    //                 $operator = $this->getDoctrine()->getRepository(Operator::class)->find($data['operatorId']);
    //                 $resume = $this->getDoctrine()->getRepository(AppointmentResume::class)->findOneBy(["operator" => $operator, "appointment" => $appointment]);
    //                 $resume->setDisabled(true);
    //                 $appointment->removeOperator($operator);
    //                 $remove = $this->getDoctrine()->getManager();
    //                 $remove->persist($appointment);
    //                 $remove->flush();
    //                 $remove->persist($resume);
    //                 $remove->flush();
    //                 $response = array(
    //                     'code'=>1,
    //                     'message'=>'Success',
    //                     'error'=>null,
    //                     'results'=>null
    //                 );
    //             } else{
    //                 $response = array(
    //                     'code'=>0,
    //                     'message'=>'Error',
    //                     'error'=>'Falta campo operatorId',
    //                     'results'=>null
    //                 );
    //             }
    //         } else{
    //             $response = array(
    //                 'code'=>0,
    //                 'message'=>'Error',
    //                 'error'=>"Falta campo appointmentId",
    //                 'results'=>null
    //             );
    //         }
    //     } catch(\Throwable $th){
    //         $response = array(
    //             'code'=>0,
    //             'message'=>'Ha ocurrido un error',
    //             'error'=>$th->getMessage(),
    //             'results'=>null
    //         );
    //     }
    //     return new JsonResponse($response, Response::HTTP_CREATED);
    //  }

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
