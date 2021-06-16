<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Operator;
use App\Entity\Service;
use App\Entity\ServiceResume;

class ServiceResumeController extends AbstractController
{

    public function __construct(JWSProviderInterface $jwsProvider){
        $this->jwsProvider = $jwsProvider;
    }

    /**
     * @param $operatorId
     * @param $serviceId
     * @param Request $request
     * @return JsonResponse
     * @Route("/attendance/service/{serviceId}&{operatorId}", name="serviceAttendance", methods="PUT")
     */
    public function serviceAttendance(Request $request, $operatorId, $serviceId){
        $data = @json_decode($request->getContent(),true);
        $token = $this->checkToken($request->headers->get('token'));
        try{
            if($token['active']){
                $service = $this->getDoctrine()->getRepository(Service::class)->find($serviceId);
                $operator = $this->getDoctrine()->getRepository(Operator::class)->find($operatorId);
                if(array_key_exists("attended",$data)){
                    $resume = $this->getDoctrine()->getRepository(ServiceResume::class)->findOneBy(['service' => $service, 'operator' => $operator]);
                    if($resume instanceof ServiceResume && $resume->getAttended() == null){
                        $resume->setAttended($data['attended']);
                        $updateResume = $this->getDoctrine()->getManager();
                        $updateResume->persist($resume);
                        $updateResume->flush();
                        $response = array(
                            "code" => 1,
                            "message" => "Success",
                            "error" => null,
                            "results" => array( "asistencia" => $resume->getAttended())
                        );
                    } else{
                        $response = array(
                            "code" => 0,
                            "message" => "Error",
                            "error" => "Ya existe un registro de asistencia, ingrese un comentario en el servicio de ser necesario",
                            "results" => null
                        );
                    }
                } else{
                    $response = array(
                        "code" => 0,
                        "message" => "Error",
                        "error" => "Falta campo attended",
                        "results" => null
                    );
                }
            } else{
                $response = array(
                    "code" => -1,
                    "message" => "Error",
                    "error" => "No access",
                    "results" => null
                );
            }
        } catch(\Throwable $th){
            $response = array(
                "code" => 0,
                "message" => "Error",
                "error" => $th->getMessage(),
                "results" => null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }






    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/api/update/operator/hours/", name="updateOperatorHours", methods={"POST"})
     */
    public function updateOperatorHours(Request $request){
        $data = @json_decode($request->getContent(),true);
        try{
            if(is_array($data) && array_key_exists("operatorId",$data)){
                $operator = $this->getDoctrine()->getRepository(Operator::class)->find($data['operatorId']);
                if(array_key_exists("serviceId",$data)){
                    $service = $this->getDoctrine()->getRepository(Service::class)->find($data['serviceId']);
                    if(array_key_exists("newEndHour",$data) && array_key_exists("newStartHour",$data)){
                        $newEndHour = \DateTime::createFromFormat("H:i",$data['newEndHour']);
                        $newStartHour = \DateTime::createFromFormat("H:i",$data['newStartHour']);
                        $workerHours = $newStartHour->diff($newEndHour);
                        $minHours = explode(":",$service->getMinHoursPerEmployees()->format("H:i"));
                        if($workerHours->h < intval($minHours[0])) $h = 0;
                        else $h = 1;
                        if($h == 1){
                            $operator->setServiceStartHour($newStartHour);
                            $operator->setServiceEndHour($newEndHour);
                            $resume = $this->getDoctrine()->getRepository(ServiceResume::class)->findOneBy(["service" => $service, "operator" => $operator]);
                            if($workerHours->h < 10) $hour = "0$workerHours->h";
                            else $hour = "$workerHours->h";
                            if($workerHours->i < 10) $min = "0$workerHours->i";
                            else $min = "$workerHours->i";
                            $operatorWorkerHours = \DateTime::createFromFormat("H:i","$hour:$min");
                            $totalServiceHours = \DateTime::createFromFormat("H:i",$service->getTotalHours());
                            $resumeList = $this->getDoctrine()->getRepository(ServiceResume::class)->findBy(["service" => $service, "accepted" => true]);
                            $totalOperatorHours = [];
                            foreach($resumeList as $operatorWorkerHour){
                                if($operatorWorkerHour->getOperator()->getId() != $operator->getId())
                                    array_push($totalOperatorHours,$operatorWorkerHour->getServiceStartHour()->diff($operatorWorkerHour->getServiceEndHour()));
                            }
                            $hour = 0;
                            $min = 0;
                            array_push($totalOperatorHours,$workerHours);
                            foreach($totalOperatorHours as $opWorkerHours){
                                if($opWorkerHours->d > 0)
                                    $hour += 24*$opWorkerHours->d;
                                $hour += $opWorkerHours->h;
                                $min += $opWorkerHours->i;
                            }
                            if($hour < 0) $h = "0$hour";
                            else $h = "$hour";
                            if($min < 0) $i = "0$min";
                            else $i = "$min";
                            $newTotalOperatorHours = \DateTime::createFromFormat("H:i","$h:$i");
                            $newDiffHours = $newTotalOperatorHours->diff($totalServiceHours);
                            if($newDiffHours->invert != 1){
                                $resume->setWorkerHours($operatorWorkerHours);
                                $resume->setServiceStartHour($newStartHour);
                                $resume->setServiceEndHour($newEndHour);
                                $add = $this->getDoctrine()->getManager();
                                $add->persist($operator);
                                $add->flush();
                                $add->persist($resume);
                                $add->flush();
                                $response = array(
                                    'code'=>1,
                                    'message'=>"success",
                                    'error'=>"Hora actualizada con exito",
                                    'results'=>null
                                );
                            } else{
                                $response = array(
                                    'code'=>0,
                                    'message'=>"Error",
                                    'error'=>"Horas totales del servicio superadas",
                                    'results'=>$newDiffHours
                                );
                            }
                        } else{
                            $response = array(
                                'code'=>0,
                                'message'=>"Error",
                                'error'=>"Ingrese un intervalo de horas valido",
                                'results'=>null
                            );
                        }
                    }
                } else{
                    $response = array(
                        'code'=>0,
                        'message'=>"Error",
                        'error'=>"Ingrese serviceId",
                        'results'=>null
                    );
                }
            } else{
                $response = array(
                    'code'=>0,
                    'message'=>"Error",
                    'error'=>"Ingrese operatorId",
                    'results'=>null
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
     * @param $operatorId
     * @param $serviceId
     * @return JsonResponse
     * @Route("/resume/service/{operatorId}&{serviceId}", name="showServiceResume", methods="GET")
     */
    public function showServiceResume($operatorId, $serviceId){
        try{
            $servicesResume = [];
            $resumes = [];
            if($operatorId){
                $resumes = $this->getDoctrine()->getRepository(ServiceResume::class)->getByOperatorId($data['operatorId']);
                // if(!empty($resumes)){
                //     foreach ($resumes as $resume){
                //         if($resume['serviceDate'] != null)
                //             $resume['serviceDate'] = $resume['serviceDate']->format('Y-m-d\TH:i:sP');
                //         if($resume['workerHours'] != null)
                //             $resume['workerHours'] = $resume['workerHours']->format('Y-m-d\TH:i:sP');
                //         $op = $this->getDoctrine()->getRepository(Operator::class)->getData($resume['operatorId']);
                //         $op['serviceStartHour'] = $resume['serviceStartHour'];
                //         $op['serviceEndHour'] = $resume['serviceEndHour'];
                //         $service = $this->getDoctrine()->getRepository(Service::class)->getById($resume['serviceId']);
                //         $operators = $this->getDoctrine()->getRepository(Operator::class)->getAllOperatorsService($service['id']);
                //         $service['operators'] = [];
                //         foreach($operators as $operator){
                //             array_push($service['operators'],$operator);
                //         }
                //         if($service['startHour'] != null)
                //             $service['startHour'] = $service['startHour']->format('Y-m-d\TH:i:sP');
                //         if($service['endHour'] != null)
                //             $service['endHour'] = $service['endHour']->format('Y-m-d\TH:i:sP');
                //         if($service['date'] != null)
                //             $service['date'] = $service['date']->format('Y-m-d\TH:i:sP');
                //         if($service['minHoursPerEmployees'] != null)
                //             $service['minHoursPerEmployees'] = $service['minHoursPerEmployees']->format('Y-m-d\TH:i:sP');
                //         $filter = $this->getDoctrine()->getRepository(UserInterests::class)->getInterests($service['filterId']);
                //         unset($service['filterId']);
                //         $service['filter'] = $filter;
                //         $resume['operator'] = $op;
                //         $resume['service'] = $service;
                //         $available = $service['maxEmployeeQuantity'] - count($operators);
                //         $resume['available'] = $available;
                //         unset($resume['operatorId']);
                //         unset($resume['serviceId']);
                //         if($service['active'])
                //             array_push($servicesResume,$resume);
                //     }
                    $response = array(
                        'code'=>1,
                        'message'=>'success',
                        'error'=>null,
                        'results'=>$resumes
                    );
                // } else {
                //     $response = array(
                //         'code'=>0,
                //         'message'=>'Error',
                //         'error'=>"No hay servicios",
                //         'results'=>null
                //     );
                // }
            } else if($serviceId){
                // $operators = $this->getDoctrine()->getRepository(Operator::class)->getAllServiceResumes($data['serviceId']);
                // $service = $this->getDoctrine()->getRepository(Service::class)->find($data['serviceId']);
                $resumes = [];
                // if(!empty($operators)){
                //     foreach ($operators as $operator){
                //         $resume = $this->getDoctrine()->getRepository(ServiceResume::class)->get($operator['resumeId']);
                //         if($resume['serviceDate'] != null)
                //             $resume['serviceDate'] = $resume['serviceDate']->format('Y-m-d\TH:i:sP');
                //         if($resume['workerHours'] != null)
                //             $resume['workerHours'] = $resume['workerHours']->format('Y-m-d\TH:i:sP');
                //         if($resume['serviceStartHour'] != null)
                //             $resume['serviceStartHour'] = $resume['serviceStartHour']->format('Y-m-d\TH:i:sP');
                //         if($resume['serviceEndHour'] != null)
                //             $resume['serviceEndHour'] = $resume['serviceEndHour']->format('Y-m-d\TH:i:sP');
                //         if($operator['serviceStartHour'] != null)
                //             $operator['serviceStartHour'] = $operator['serviceStartHour']->format('Y-m-d\TH:i:sP');
                //         if($operator['serviceEndHour'] != null)
                //             $operator['serviceEndHour'] = $operator['serviceEndHour']->format('Y-m-d\TH:i:sP');
                //         $operatorInfo = $this->getDoctrine()->getRepository(UserInfo::class)->getData($operator['operatorData']);
                //         $operator['nombre'] = $operatorInfo['firstName'];
                //         $resume['operator'] = $operator;
                //         if($resume['accepted'] !== null && $service->getActive())
                //             array_push($resumes,$resume);
                //     }
                    $response = array(
                        'code'=>1,
                        'message'=>'success',
                        'error'=>null,
                        'results'=>$resumes
                    );
                // } else {
                //     $response = array(
                //         'code'=>0,
                //         'message'=>'Error',
                //         'error'=>"No hay servicios",
                //         'results'=>null
                //     );
                // }
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
