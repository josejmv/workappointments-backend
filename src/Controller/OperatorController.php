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
use App\Entity\Appointment;
use App\Entity\AppointmentResume;
use App\Entity\User;

class OperatorController extends AbstractController
{

    public function __construct(JWSProviderInterface $jwsProvider){
        $this->jwsProvider = $jwsProvider;
    }
    
    // /**
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @Route("/api/show/users/operators/", name="showOperators", methods={"POST"})
    //  */
    // public function showOperators(Request $request){
    //     $data = @json_decode($request->getContent(),true);
    //     if(is_array($data) && array_key_exists("page", $data)){
    //         if(array_key_exists("active",$data)){
    //             try{
    //                 $start = 52 * ($data['page'] - 1);
    //                 $index = 1 + $start;
    //                 $end = 52 * $data["page"];
    //                 $aux = 0;
    //                 $totalOperators = count($this->getDoctrine()->getRepository(Operator::class)->findAll());
    //                 $operators = [];
    //                 if($data['active'] === true){
    //                     while($index <= $end && $aux <= 10){
    //                         $operator = $this->getDoctrine()->getRepository(Operator::class)->getData($index);
    //                         $operatorData = $this->getDoctrine()->getRepository(User::class)->getData($operator['userId']);
    //                         $operatorData = @json_decode($this->get('serializer')->serialize($operatorData,'json',[
    //                             'circular_reference_handler' => function ($object) {
    //                                 return $object->getId();
    //                             }
    //                         ]),true);
    //                         $operator['operatorData'] = $operatorData;
    //                         unset($operator['userId']);
    //                         if($operator['active']){
    //                             array_push($operators,$operator);
    //                             $aux = 0;
    //                         } else $aux++;
    //                         $index++;
    //                     }
    //                 } else if($data['active'] === false) {
    //                     while($index <= $totalOperators){
    //                         $operator = $this->getDoctrine()->getRepository(Operator::class)->getData($index);
    //                         $operatorData = $this->getDoctrine()->getRepository(User::class)->getData($operator['userId']);
    //                         $operatorData = @json_decode($this->get('serializer')->serialize($operatorData,'json',[
    //                             'circular_reference_handler' => function ($object) {
    //                                 return $object->getId();
    //                             }
    //                         ]),true);
    //                         $operator['operatorData'] = $operatorData;
    //                         unset($operator['userId']);
    //                         if(!$operator['active'])
    //                             array_push($operators,$operator);
    //                         $index++;
    //                     }
    //                 }
    //                 $response = array(
    //                     'code'=>1,
    //                     'message'=>"Success",
    //                     'error'=>null,
    //                     'results'=>$operators
    //                 );
    //             } catch(\Throwable $th){
    //                 $response = array(
    //                     'code'=>0,
    //                     'message'=>"Error",
    //                     'error'=>$th->getMessage(),
    //                     'results'=>null
    //                 );
    //             }
    //         } else{
    //             $response = array(
    //                 'code'=>0,
    //                 'message'=>"Error",
    //                 'error'=>"Falta campo active",
    //                 'results'=>null
    //             );
    //         }
    //     } else {
    //         $response = array(
    //             'code'=>0,
    //             'message'=>"Error",
    //             'error'=>"Falta campo page",
    //             'results'=>null
    //         );
    //     }
    //     return new JsonResponse($response, Response::HTTP_CREATED);
    // }
    
    // /**
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @Route("/api/search/operators/", name="searchOperators", methods={"POST"})
    //  */
    // public function searchOperators(Request $request){
    //     $data = @json_decode($request->getContent(),true);
    //     if(is_array($data) && array_key_exists("filter",$data)){
    //         try{
    //             $users = $this->getDoctrine()->getRepository(User::class)->search($data['filter']);
    //             if($users != null){
    //                 $operators = [];
    //                 foreach($users as $user){
    //                     $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
    //                     if($operator != null)
    //                         if($operator->getActive()){
    //                             $op = [];
    //                             $op['id'] = $operator->getId();
    //                             $op['operatorData'] = $user;
    //                             array_push($operators,$op);
    //                         }
    //                 }
    //                 $operatorsData = $this->get('serializer')->serialize($operators,'json',[
    //                     'circular_reference_handler' => function ($object) {
    //                         return $object->getId();
    //                     }
    //                 ]);
    //                 $response = array(
    //                     'code'=>1,
    //                     'message'=>"Success",
    //                     'error'=>null,
    //                     'results'=>json_decode($operatorsData)
    //                 );
    //             } else{
    //                 $usersInfo = $this->getDoctrine()->getRepository(UserInfo::class)->searchByFirstName($data['filter']);
    //                 if($usersInfo != null){
    //                     $operators = [];
    //                     foreach($usersInfo as $userInfo){
    //                         $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["userInfo" => $userInfo]);
    //                         $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
    //                         if($operator != null)
    //                             if($operator->getActive())
    //                                 array_push($operators,$operator);
    //                     }
    //                     $operatorsData = $this->get('serializer')->serialize($operators,'json',[
    //                         'circular_reference_handler' => function ($object) {
    //                             return $object->getId();
    //                         }
    //                     ]);
    //                     $response = array(
    //                         'code'=>1,
    //                         'message'=>"Success",
    //                         'error'=>null,
    //                         'results'=>json_decode($operatorsData)
    //                     );
    //                 } else{
    //                     $usersInfo = $this->getDoctrine()->getRepository(UserInfo::class)->searchByCelphone($data['filter']);
    //                     if($usersInfo != null){
    //                         $operators = [];
    //                         foreach($usersInfo as $userInfo){
    //                             $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["userInfo" => $userInfo]);
    //                             $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
    //                             if($operator != null)
    //                                 if($operator->getActive())
    //                                     array_push($operators,$operator);
    //                         }
    //                         $operatorsData = $this->get('serializer')->serialize($operators,'json',[
    //                             'circular_reference_handler' => function ($object) {
    //                                 return $object->getId();
    //                             }
    //                         ]);
    //                         $response = array(
    //                             'code'=>1,
    //                             'message'=>"Success",
    //                             'error'=>null,
    //                             'results'=>json_decode($operatorsData)
    //                         );
    //                     } else{
    //                         $response = array(
    //                             'code'=>0,
    //                             'message'=>"Error",
    //                             'error'=>"Operario no encontrado",
    //                             'results'=>null
    //                         );
    //                     }
    //                 }
    //             }
    //         } catch(\Throwable $th){
    //             $response = array(
    //                 'code'=>0,
    //                 'message'=>"Error",
    //                 'error'=>$th->getMessage(),
    //                 'results'=>null
    //             );
    //         }
    //     } else{
    //         $response = array(
    //             'code'=>0,
    //             'message'=>"Error",
    //             'error'=>"Falta campo filter",
    //             'results'=>null
    //         );
    //     }
    //     return new JsonResponse($response, Response::HTTP_CREATED);
    // }
    


// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------



     /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @Route("/accept/service/{id}/", name="addOperatorToService", methods="PUT")
     */
    public function addOperatorToService(Request $request, $id){
        $data = @json_decode($request->getContent(),true);
        $token = $this->checkToken($request->headers->get('token'));
        try{
            if($token['active']){
                $service = $this->getDoctrine()->getRepository(Service::class)->find($id);
                if($service instanceof Service){
                    if($service->getActive()){
                        $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                        $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
                        if($operator instanceof Operator){
                            if($user->getActive()){
                                if(count($service->getOperators()) < $service->getMaxEmployeeQuantity()){
                                    if(is_array($data) && array_key_exists('start',$data) && array_key_exists('end',$data)){
                                        $startHour = new \DateTime($data['start']);
                                        $endHour = new \DateTime($data['end']);
                                        $workerHours = $startHour->diff($endHour);
                                        if($workerHours->invert != 1){
                                            $hoursAux = new \DateTime($service->getStartHour()->format("H:i"));
                                            $hoursAux = $hoursAux->diff($startHour);
                                            if($hoursAux->invert != 1){
                                                $hoursAux = new \DateTime($service->getEndHour()->format("H:i"));
                                                $hoursAux = $hoursAux->diff($endHour);
                                                if($hoursAux->invert == 1 || ($hoursAux->h == 0 && $hoursAux->i == 0)){
                                                    $hoursAux = new \DateTime($service->getMinHoursPerEmployees()->format("H:i"));
                                                    $workerHours = new \DateTime("$workerHours->h:$workerHours->i");
                                                    $hoursAux = $hoursAux->diff($workerHours);
                                                    if($hoursAux->invert != 1){
                                                        $service->addOperator($operator);
                                                        $resume = $this->getDoctrine()->getRepository(ServiceResume::class)->findOneBy(["service" => $service, "operator" => $operator]);
                                                        if($resume instanceof ServiceResume){
                                                            if($resume->getAccepted() == null){
                                                                $resume->setServiceStartHour($startHour);
                                                                $resume->setServiceEndHour($endHour);
                                                                $totalServiceHours = $service->getTotalHours();
                                                                $resumeList = $this->getDoctrine()->getRepository(ServiceResume::class)->findBy(["service" => $service, "accepted" => true]);
                                                                $totalOperatorHours = [];
                                                                foreach($resumeList as $operatorWorkerHour){
                                                                    $workerHoursByOperators = $operatorWorkerHour->getServiceStartHour()->diff($operatorWorkerHour->getServiceEndHour());
                                                                    array_push($totalOperatorHours, $workerHoursByOperators);
                                                                }
                                                                array_push($totalOperatorHours,$startHour->diff($endHour));
                                                                $hour = 0; $min = 0;
                                                                foreach($totalOperatorHours as $operatorWorkerHour){
                                                                    if($operatorWorkerHour->d > 0) $hour += 24 * $operatorWorkerHour->d;
                                                                    $hour += $operatorWorkerHour->h;
                                                                    $min += $operatorWorkerHour->i;
                                                                }
                                                                $newTotalOperatorHours = new \DateTime(date("H:i", mktime($hour,$min)));
                                                                $newDiffHours = $newTotalOperatorHours->diff($totalServiceHours);
                                                                if($newDiffHours->invert != 1){
                                                                    $resume->setWorkerHours($validateWorkerHours);
                                                                    $resume->setAccepted(true);
                                                                    $add = $this->getDoctrine()->getManager();
                                                                    $add->persist($resume);
                                                                    $add->persist($service);
                                                                    $add->flush();
                                                                    $code = 1;
                                                                    $message = "Success";
                                                                    $error = null;
                                                                    $result = null;
                                                                } else{
                                                                    $code = 0;
                                                                    $message = "Error";
                                                                    $error = "Horas totales del servicio superadas";
                                                                    $result = null;
                                                                }
                                                            } else{
                                                                $code = 0;
                                                                $message = "Error";
                                                                $error = "Ya existe un registro para esta relacion servicio/operario";
                                                                $result = null;
                                                            }
                                                        } else{
                                                            $code = 0;
                                                            $message = "Error";
                                                            $error = "Usted no ha sido seleccionado para participar en este servicio";
                                                            $result = null;
                                                        }


                                                        $code = 1;
                                                        $message = "Success";
                                                        $error = null;
                                                        $result = null;
                                                    } else{
                                                        $code = 0;
                                                        $message = "Error";
                                                        $error = "El intervalo de horas ingresado es menor a las horas minimas por operario";
                                                        $result = null;
                                                    }
                                                } else{
                                                    $code = 0;
                                                    $message = "Error";
                                                    $error = "Debe ingresar una hora de salida valida";
                                                    $result = null;
                                                }
                                            } else{
                                                $code = 0;
                                                $message = "Error";
                                                $error = "Debe ingresar una hora de entrada valida";
                                                $result = null;
                                            }
                                        } else{
                                            $code = 0;
                                            $message = "Error";
                                            $error = "Ingrese un intervalo de horas valido";
                                            $result = null;
                                        }
                                    } else{
                                        $code = 0;
                                        $message = "Error";
                                        $error = "faltan campos start o end";
                                        $result = null;
                                    }
                                } else{
                                    $code = 0;
                                    $message = "Error";
                                    $error = "No hay disponibilidad";
                                    $result = null;
                                }
                            } else{
                                $code = 0;
                                $message = "Error";
                                $error = "Operario en lista negra";
                                $result = null;
                            }
                        } else{
                            $code = 0;
                            $message = "Error";
                            $error = "Operario no encontrado";
                            $result = null;
                        }
                    } else{
                        $code = 0;
                        $message = "Error";
                        $error = "Servicio no disponible";
                        $result = null;
                    }
                } else {
                    $code = 0;
                    $message = "Error";
                    $error = "Servicio no encontrado";
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
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @Route("/refuse/service/{id}/", name="refuseService", methods="PUT")
     */
    public function refuseService(Request $request, $id){
        $token = $this->checkToken($request->headers->get('token'));
        try{
            if($token['active']){
                $service = $this->getDoctrine()->getRepository(Service::class)->find($id);
                $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(['operatorData' => $user]);
                $resume = $this->getDoctrine()->getRepository(ServiceResume::class)->findOneBy(["service" => $service, "operator" => $operator]);
                if($resume instanceof ServiceResume){
                    if($resume->getAccepted() == null){
                        $resume->setAccepted(false);
                        $refuse = $this->getDoctrine()->getManager();
                        $refuse->persist($resume);
                        $refuse->flush();
                        $response = array(
                            "code" => 1,
                            "message" => "Success",
                            "error" => null,
                            "results" => null
                        );
                    } else{
                        $response = array(
                            "code" => 0,
                            "message" => "Error",
                            "error" => "Ya existe un registro para esta relacion servicio/operario",
                            "results" => null
                        );
                    }
                } else{
                    $response = array(
                        "code" => 0,
                        "message" => "Error",
                        "error" => "Usted no ha sido seleccionado para participar en este servicio",
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
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @Route("/accept/appointment/{id}/", name="addOperatorToAppointment", methods="PUT")
     */
    public function addOperatorToAppointment(Request $request, $id){
        $token = $this->checkToken($request->headers->get('token'));
        try{
            if($token['active']){
                $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($id);
                if($appointment instanceof Appointment){
                    if($appointment->getActive()){
                        $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                        $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
                        if($operator instanceof Operator){
                            if($user->getActive()){
                                if(count($appointment->getOperators()) < $appointment->getQuantityEmployees()){
                                    $appointment->addOperator($operator);
                                    $resume = $this->getDoctrine()->getRepository(AppointmentResume::class)->findOneBy(["appointment" => $appointment, "operator" => $operator]);
                                    if($resume instanceof AppointmentResume){
                                        if($resume->getAccepted() == null){
                                            $resume->setAccepted(true);
                                            $add = $this->getDoctrine()->getManager();
                                            $add->persist($resume);
                                            $add->flush();
                                            $code = 1;
                                            $message = "success";
                                            $error = null;
                                            $result = null;
                                        } else{
                                            $code = 0;
                                            $message = "Error";
                                            $error = "Ya existe un registro para esta relacion cita/operario";
                                            $result = null;
                                        }
                                    } else{
                                        $code = 0;
                                        $message = "Error";
                                        $error = "Usted no ha sido seleccionado para esta cita de entrevista";
                                        $result = null;
                                    }
                                } else{
                                    $code = 0;
                                    $message = "Error";
                                    $error = "No hay disponibilidad";
                                    $result = null;
                                }
                            } else {
                                $code = 0;
                                $message = "Error";
                                $error = "Operario en lista negra";
                                $result = null;
                            }
                        } else {
                            $code = 0;
                            $message = "Error";
                            $error = "Operario no encontrado";
                            $result = null;
                        }
                    } else{
                        $code = 0;
                        $message = "Error";
                        $error = "Cita no disponible";
                        $result = null;
                    }
                } else {
                    $code = 0;
                    $message = "Error";
                    $error = "Cita no encontrada";
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
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @Route("/refuse/appointment/{id}/", name="refuseAppointment", methods="PUT")
     */
    public function refuseAppointment(Request $request, $id){
        $data = @json_decode($request->getContent(),true);
        $token = $this->checkToken($request->headers->get('token'));
        try{
            if($token['active']){
                $appointment = $this->getDoctrine()->getRepository(Appointment::class)->find($id);
                $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                $operator = $this->getDoctrine()->getRepository(Operator::class)->findOneBy(["operatorData" => $user]);
                $resume = $this->getDoctrine()->getRepository(AppointmentResume::class)->findOneBy(["appointment" => $appointment, "operator" => $operator]);
                if($resume instanceof AppointmentResume){
                    if($resume->getAccepted() == null){
                        $resume->setAccepted(false);
                        $refuse = $this->getDoctrine()->getManager();
                        $refuse->persist($resume);
                        $refuse->flush();
                        $response = array(
                            "code" => 1,
                            "message" => "Success",
                            "error" => null,
                            "results" => null
                        );
                    } else{
                        $response = array(
                            "code" => 0,
                            "message" => "Error",
                            "error" => "Ya existe un registro para esta relacion cita/operario",
                            "results" => null
                        );
                    }
                } else{
                    $response = array(
                        "code" => 0,
                        "message" => "Error",
                        "error" => "Usted no ha sido seleccionado para participar en esta cita",
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
