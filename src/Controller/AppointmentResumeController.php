<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Operator;
use App\Entity\Appointment;
use App\Entity\AppointmentResume;

class AppointmentResumeController extends AbstractController
{
    // /**
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @Route("/api/show/appointmentResume/", name="showAppointmentResume", methods={"POST"})
    //  */
    // public function showAppointmentResume(Request $request){
    //     $data = @json_decode($request->getContent(),true);
    //     try{
    //         $appointmentsResume = [];
    //         $resumes = [];
    //         if(is_array($data) && array_key_exists("operatorId",$data)){
    //             $resumes = $this->getDoctrine()->getRepository(AppointmentResume::class)->getByOperatorId($data['operatorId']);
    //             if(!empty($resumes)){
    //                 foreach ($resumes as $resume){
    //                     $operator = $this->getDoctrine()->getRepository(Operator::class)->getData($resume['operatorId']);
    //                     $appointment = $this->getDoctrine()->getRepository(Appointment::class)->getById($resume['appointmentId']);
    //                     $appointment['date'] = $appointment['date']->format('Y-m-d\TH:i:sP');
    //                     $appointment['hour'] = $appointment['hour']->format('Y-m-d\TH:i:sP');
    //                     if($appointment['active']){
    //                         $resume['operator'] = $operator;
    //                         $resume['appointment'] = $appointment;
    //                         unset($resume['operatorId']);
    //                         unset($resume['appointmentId']);
    //                         array_push($appointmentsResume,$resume);
    //                     }
    //                 }
    //                 $response = array(
    //                     'code'=>1,
    //                     'message'=>'success',
    //                     'error'=>null,
    //                     'results'=>$appointmentsResume
    //                 );
    //             } else {
    //                 $response = array(
    //                     'code'=>0,
    //                     'message'=>'Error',
    //                     'error'=>"No hay citas",
    //                     'results'=>$data
    //                 );
    //             }
    //         } else if(is_array($data) && array_key_exists("appointmentId",$data)){
    //             $operators = $this->getDoctrine()->getRepository(Operator::class)->getAllAppointmentResumes($data['appointmentId']);
    //             $resumes = [];
    //             if(!empty($operators)){
    //                 foreach ($operators as $operator){
    //                     $resume = $this->getDoctrine()->getRepository(AppointmentResume::class)->get($operator['resumeId']);
    //                     $operatorInfo = $this->getDoctrine()->getRepository(UserInfo::class)->getData($operator['operatorData']);
    //                     $operator['nombre'] = $operatorInfo['firstName'];
    //                     $resume['operator'] = $operator;
    //                     if($resume['accepted'] !== null)
    //                         array_push($resumes,$resume);
    //                 }
    //                 $response = array(
    //                     'code'=>1,
    //                     'message'=>'Success',
    //                     'error'=>null,
    //                     'results'=>$resumes
    //                 );
    //             } else {
    //                 $response = array(
    //                     'code'=>0,
    //                     'message'=>'Error',
    //                     'error'=>"No hay servicios",
    //                     'results'=>null
    //                 );
    //             }
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
    // }


    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/api/register/appointment/resume/", name="registerAppointmentResume", methods={"POST"})
     */
     public function registerAppointmentResume(Request $request){
         $data = @json_decode($request->getContent(),true);
         try{
             if(is_array($data) && array_key_exists("appointmentId", $data)){
                 if(array_key_exists('operatorId',$data)){
                     $operator = $this->getDoctrine()->getRepository(Operator::class)->find($data['operatorId']);
                     $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($data['appointmentId']);
                     $resume = $this->getDoctrine()->getRepository(AppointmentResume::class)->findOneBy(["operator" => $operator, "appointment" => $appointment]);
                     if($resume instanceof AppointmentResume){
                         if(array_key_exists('attended',$data)){
                             $resume->setPersonal($data['attended']);
                             $updateResume = $this->getDoctrine()->getManager();
                             $updateResume->persist($resume);
                             $updateResume->flush();
                             $response = array(
                                "code" => 1,
                                "message" => "Success",
                                "error" => null,
                                "response" => $resume->getPersonal(),
                             );
                         } else{
                             $response = array(
                                "code" => 0,
                                "message" => "Error",
                                "error" => 'falta campo attended',
                                "response" => null,
                             );
                         }
                        //  if(array_key_exists('appointmentResume',$data)){
                        //      $resumeData = json_encode($data['appointmentResume']);
                        //      $resumeData = $this->get('serializer')->deserialize($resumeData,AppointmentResume::class,'json');
                        //      $resume->setPersonal($resumeData->getPersonal());
                        //      $resume->setStatus($resumeData->getStatus());
                        //      $resume->setFirstPosition($resumeData->getFirstPosition());
                        //      $resume->setInterviewer($resumeData->getInterviewer());
                        //      $resume->setSecondPosition($resumeData->getSecondPosition());
                        //      $resume->setInterviewer2($resumeData->getInterviewer2());
                        //      $resume->setExpensive($resumeData->getExpensive());
                        //      $resume->setWantWorkWithUs($resumeData->getWantWorkWithUs());
                        //      $resume->setFirstStep($resumeData->getFirstStep());
                        //      $resume->setCitedForPersonal($resumeData->getCitedForPersonal());
                        //      $updateResume = $this->getDoctrine()->getManager();
                        //      $updateResume->persist($resume);
                        //      $updateResume->flush();
                        //      $response = array(
                        //         "code" => 1,
                        //         "message" => "Success",
                        //         "error" => null,
                        //         "response" => null,
                        //      );
                        //  } else{
                        //      $response = array(
                        //         "code" => 0,
                        //         "message" => "Error",
                        //         "error" => 'falta campo appointmentResume',
                        //         "response" => null,
                        //      );
                        //  }
                     } else{
                         $response = array(
                            "code" => 0,
                            "message" => "Error",
                            "error" => 'Registro no encontrado, verifique el serviceId y el operatorId',
                            "response" => null,
                         );
                     }
                 } else{
                     $response = array(
                        "code" => 0,
                        "message" => "Error",
                        "error" => 'falta campo operatorId',
                        "response" => null,
                     );
                 }
             } else{
                 $response = array(
                    "code" => 0,
                    "message" => "Error",
                    "error" => 'falta campo appointmentId',
                    "response" => null,
                 );
             }
         } catch(\Throwable $th){
             $response = array(
                "code" => 0,
                "message" => "Error",
                "error" => $th->getMessage(),
                "response" => null,
             );
         }
         return new JsonResponse($response, Response::HTTP_CREATED);
     }

    //  /**
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @Route("/api/show/appointmentResume/", name="showAppointmentResume", methods={"POST"})
    //  */
    // public function showAppointmentResume(Request $request){
    //     $data = @json_decode($request->getContent(),true);
    //     $token = $request->headers->get('apiToken');
    //     try{
    //         $result = @json_decode($this->get('serializer')->serialize($this->jwsProvider->load($token),'json',[]),true);
    //         $user = $this->getDoctrine()->getRepository(User::class)->find($result['payload']['username']);
    //         $appointmentResume = [];
    //         $resumes = [];
    //         if($user->getRol() === "Operario"){
    //             $entity = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
    //             $resumes = $entity->getAppointmentResume();
    //         } else if(is_array($data) && array_key_exists("appointmentId",$data)){
    //             $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($data['appointmentId']);
    //             $resumes = $appointment->getAppointmentResume();
    //         }
    //         if(!empty($resumes)){
    //             foreach ($resumes as $resume){
    //                 $resume = @json_decode($this->get('serializer')->serialize($resume,'json',[
    //                     'circular_reference_handler' => function ($object) {
    //                         return $object->getId();
    //                     }
    //                 ]),true);
    //                 array_push($appointmentResume,$resume);
    //             }
    //             $response = array(
    //                 'code'=>1,
    //                 'message'=>'success',
    //                 'error'=>null,
    //                 'results'=>$appointmentResume
    //             );
    //         } else {
    //             $response = array(
    //                 'code'=>0,
    //                 'message'=>'Error',
    //                 'error'=>"No hay servicios",
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
    // }
}
