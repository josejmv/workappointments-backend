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
use App\Entity\Service;
use App\Entity\ServiceResume;
use App\Entity\User;

class ServiceController extends AbstractController
{   
    public function __construct(JWTEncoderInterface $jwtEncoder, JWSProviderInterface $jwsProvider){
        $this->jwtEncoder = $jwtEncoder;
        $this->jwsProvider = $jwsProvider;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/service/", name="addService", methods="POST")
     */
    public function addService(Request $request){
        $data = @json_decode($request->getContent(),true);
        $token = $this->checkToken($request->headers->get('token'));
        try {
            if($token['active']){
                $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                $rol = $user->getRol();
                if(array_key_exists("service", $data) && array_key_exists("filter", $data)){
                    $serviceData = json_encode($data["service"]);
                    $filter = json_encode($data["filter"]);
                    $entity = null;
                    if($rol === "Admin")
                        $entity = $this->getDoctrine()->getRepository(Admin::class)->findOneBy(['adminData' => $user]);
                    if($rol === "Manager")
                        $entity = $this->getDoctrine()->getRepository(Manager::class)->find(['managerData' => $user]);
                    if($entity != null){
                        $service = $this->get("serializer")->deserialize($serviceData, Service::class,'json');
                        $startHour = new \DateTime($service->getStartHour()->format('H:i'));
                        $endHour = new \DateTime($service->getEndHour()->format('H:i'));
                        $hoursDiff = $startHour->diff($endHour);
                        $h = 0;
                        if($hoursDiff->d > 0) $h += 24 * $hoursDiff->d;
                        $h += $hoursDiff->h;
                        $i = $hoursDiff->i;
                        $journalHours = new \DateTime("$h:$i");
                        if($hoursDiff->invert != 1){
                            $minHoursPerEmployee = new \DateTime($service->getMinHoursPerEmployees()->format('H:i'));
                            $verify = $journalHours->diff($minHoursPerEmployee);
                            if( ($verify->y == 0 &&
                                $verify->m == 0 &&
                                $verify->d == 0 &&
                                $verify->h == 0 &&
                                $verify->i == 0 &&
                                $verify->s == 0) ||
                                $verify->invert == 1
                            ){
                                $h *= $service->getDays();
                                $totalHours = new \DateTime(date("d-m-Y H:i",mktime($h, $i)));
                                $service->setTotalHours($totalHours);
                                $service->setFilter($filter);
                                $service->setActive(true);
                                $quantityServices = count($this->getDoctrine()->getRepository(Service::class)->findAll());
                                $token = $this->jwtEncoder->encode([
                                    'id' => $quantityServices + 1,
                                    'exp' => time() + 3600,
                                    'verify' => true
                                ]);
                                $service->setNewCreated($token);
                                $entity->addService($service);
                                try {
                                    $newService = $this->getDoctrine()->getManager();
                                    $newService->persist($entity);
                                    $newService->flush();
                                    $response = array(
                                        'code'=>1,
                                        'message'=>'Success',
                                        'error'=>null,
                                        'results'=> $token
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
                                'error'=> "El intervalo de horas ingresado es inferior a las horas minimas por empleado",
                                'results'=>null
                            );
                        } else $response = array(
                            'code'=>0,
                            'message'=>'Error',
                            'error'=>"Ingrese un intervalo de horas valido",
                            'results'=>null
                        );
                    } else $response = array(
                        'code'=>0,
                        'message'=>'Error',
                        'error'=>"No tiene permisos para crear servicios",
                        'results'=>null
                    );
                } else $response = array(
                            'code'=>0,
                            'message'=>'Error',
                            'error'=>"Falta campo service o filter",
                            'results'=>null
                        );
            } else $response = array(
                        'code'=>-1,
                        'message'=>'Error',
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
     * @Route("/paginator/services/", name="servicesPaginator", methods="GET")
     */
    public function servicesPaginator(){
        try{
            $servicesLimitPage = count($this->getDoctrine()->getRepository(Service::class)->findAll());
            $servicesLimitPage = round($servicesLimitPage / 20);
            if($servicesLimitPage < 1) $servicesLimitPage = 1;
            $response = array(
                'code'=>1,
                'message'=>'Success',
                'error'=>null,
                'results'=>$servicesLimitPage
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
     * @Route("/services/{page}&{active}/", name="showServices", methods="GET")
     */
    public function showServices($page, $active){
        try{
            $services = $this->getDoctrine()->getRepository(Service::class)->findBy(["active" => $active === "true" ? true : false], null, 20, ($page - 1 ) * 20);
            if(!empty($services)){
                $operators = [];
                foreach ($services as $service){
                    $formatedService = @json_decode($this->get('serializer')->serialize($service,'json',[
                        'circular_reference_handler' => function ($object) {
                            return $object->getId();
                        }
                    ]),true);
                    $hours = 0; $min = 0;
                    foreach($formatedService['operators'] as $operator){
                        $operators[] = $operator->getId();
                        $resume = $this->getDoctrine()->getRepository(ServiceResume::class)->findOneBy(["service" => $singleService, "operator" => $operator]);
                        $workerHour = $resume->getServiceStartHour()->diff($resume->getServiceEndHour());
                        $hours += $workerHour->h;
                        if($workerHour->d > 0) $hours += 24 * $workerHour->d;
                        $min += $workerHour->i;
                        if($min >= 60) {$hours += 1; $min -= 60;}
                    }
                    $time = mktime($hours, $min, 0);
                    $time = new \DateTime(date("Y-m-d G:i", $time));
                    $now = mktime(0, 0, 0);
                    $now = new \DateTime(date("Y-m-d G:i", $now));
                    $lessHours = $now->diff($time);
                    $lessHours = new \DateTime("$lessHours->h:$lessHours->i");
                    $formatedService['startHour'] = $service->getStartHour()->format('H:i');
                    $formatedService['endHour'] = $service->getEndHour()->format('H:i');
                    $formatedService['totalHours'] = $service->getTotalHours()->format('d H:i');
                    $formatedService['date'] = $service->getDate()->format('d-m-Y');
                    $formatedService['minHoursPerEmployees'] = $service->getMinHoursPerEmployees()->format('H:i');
                    $formatedService['lessHours'] = $lessHours->format('d H:i');
                    $formatedService['filter'] = json_decode($formatedService['filter']);
                    unset($formatedService['operators']);
                    unset($formatedService['serviceResume']);
                    unset($formatedService['newCreated']);
                    if($formatedService['active'] == $active){
                        $available = $formatedService['maxEmployeeQuantity'] - count($operators);
                        $formatedService["available"] = $available;
                    }
                    $activeServices[] = $formatedService;
                }
                $response = array(
                    'code'=>1,
                    'message'=>'success',
                    'error'=>null,
                    'results'=> $activeServices
                );
            } else {
                $response = array(
                    'code'=>1,
                    'message'=>'success',
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
     * @Route("/service/{id}/", name="showSingleService", methods="GET")
     */
    public function showSingleService(Request $request, $id){
        try{
            $service = $this->getDoctrine()->getRepository(Service::class)->find($id);
            if($service instanceof Service && $service->getActive()){
                $formatedService = @json_decode($this->get('serializer')->serialize($service,'json',[
                    'circular_reference_handler' => function ($object) {
                        return $object->getId();
                    }
                ]),true);
                $formatedService['serviceResume'] = [];
                unset($formatedService['operators']);
                unset($formatedService['newCreated']);
                $formatedService['available'] = $service->getMaxEmployeeQuantity() - count($service->getOperators());
                $formatedService['filter'] = json_decode($formatedService['filter']);
                $formatedService['startHour'] = $service->getStartHour()->format('H:i');
                $formatedService['endHour'] = $service->getEndHour()->format('H:i');
                $formatedService['totalHours'] = $service->getTotalHours()->format('d H:i');
                $formatedService['date'] = $service->getStartHour()->format('d-m-Y');
                $formatedService['minHoursPerEmployees'] = $service->getMinHoursPerEmployees()->format('H:i');
                foreach ($service->getOperators() as $operator) {
                    $formatedService['operators']['id'] = $operator->getId();
                    $formatedService['operators']['email'] = $operator->getEmail();
                }
                $code = 1;
                $message = "success";
                $error = null;
                $result = $formatedService;
            } else{
                $code = 0;
                $message = "Error";
                $error = "Servicio no encontrado";
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
     * @Route("/service/{id}/", name="deleteService", methods="DELETE")
     */
    public function deleteService(Request $request, $id){
        $data = @json_decode($request->getContent(),true);
        $token = $this->checkToken($request->headers->get('token'));
        try{
            if($token['active']){
                $user = $this->getDoctrine()->getRepository(User::class)->find($token['data']['id']);
                if($user->getRol() === "Manager" || $user->getRol() === "Admin"){
                    $service = $this->getDoctrine()->getRepository(Service::class)->find($id);
                    if($service instanceof Service){
                        $resumes = $this->getDoctrine()->getRepository(ServiceResume::class)->findBy(["service" => $service]);
                        $service->setActive(!$service->getActive());
                        $service->removeAllOperator();
                        $delete = $this->getDoctrine()->getManager();
                        $delete->persist($service);
                        $delete->flush();
                        foreach ($resumes as $resume) {
                            $delete->remove($resume);
                            $delete->flush();
                        }
                        $code = 1;
                        $message = "success";
                        $error = null;
                        $result = null;
                    } else{
                        $code = 0;
                        $message = "error";
                        $error = "Servicio no encontrado";
                        $result = null;
                    }
                } else{
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
                'message'=>'Ha ocurrido un error inesperado',
                'error'=>$th->getMessage(),
                'results'=>null
            );
        }
        return new JsonResponse($response, Response::HTTP_CREATED);
    }
    
    /**
     * @param $title
     * @return JsonResponse
     * @Route("/search/services/{title}/", name="searchServices", methods="GET")
     */
     public function searchServices($title){
        try{
            $services = $this->getDoctrine()->getRepository(Service::class)->search($title);
            $servicesPerFilter = @json_decode($this->get('serializer')->serialize($services,'json',[
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]),true);
            if(!empty($servicesPerFilter)){
                foreach($servicesPerFilter as $key => $singleService){
                    $singleService['startHour'] = $services[$key]->getStartHour()->format('H:i');
                    $singleService['endHour'] = $services[$key]->getEndHour()->format('H:i');
                    $singleService['date'] = $services[$key]->getDate()->format('d-m-Y');
                    $singleService['minHoursPerEmployees'] = $services[$key]->getMinHoursPerEmployees()->format('H:i');
                    $singleService['totalHours'] = $services[$key]->getTotalHours()->format('d H:i');
                    $singleService['filter'] = json_decode($services[$key]->getFilter());
                    $hours = 0; $min = 0; $operators = [];
                    foreach($singleService['operators'] as $operator){
                        $operators[] = $operator->getId();
                        $resume = $this->getDoctrine()->getRepository(ServiceResume::class)->findOneBy(["service" => $services[$key], "operator" => $operator]);
                        $workerHour = $resume->getServiceStartHour()->diff($resume->getServiceEndHour());
                        $hours += $workerHour->h;
                        if($workerHour->d > 0) $hours += 24 * $workerHour->d;
                        $min += $workerHour->i;
                        if($min >= 60) {$hours += 1; $min -= 60;}
                    }
                    $time = mktime($hours, $min, 0);
                    $time = new \DateTime(date("Y-m-d G:i", $time));
                    $now = mktime(0, 0, 0);
                    $now = new \DateTime(date("Y-m-d G:i", $now));
                    $lessHours = $now->diff($time);
                    $lessHours = new \DateTime("$lessHours->h:$lessHours->i");
                    $singleService['lessHours'] = $lessHours->format('d H:i');
                    $singleService['available'] = $singleService['maxEmployeeQuantity'] - count($operators);
                    unset($singleService['operators']);
                    unset($singleService['serviceResume']);
                    unset($singleService['newCreated']);
                    $res[] = $singleService;
                }
            } else $res = [];
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
    //  * @Route("/api/remove/service/operator/", name="removeOperatorToService", methods={"POST"})
    //  */
    //  public function removeOperatorToService(Request $request){
    //     $data = @json_decode($request->getContent(),true);
    //     try{
    //         if(is_array($data) && array_key_exists("serviceId", $data)){
    //             if(array_key_exists("operatorId", $data)){
    //                 $service = $this->getDoctrine()->getRepository(Service::class)->find($data['serviceId']);
    //                 $operator = $this->getDoctrine()->getRepository(Operator::class)->find($data['operatorId']);
    //                 $resume = $this->getDoctrine()->getRepository(ServiceResume::class)->findOneBy(["operator" => $operator, "service" => $service]);
    //                 $service->removeOperator($operator);
    //                 $resume->setDisabled(true);
    //                 $remove = $this->getDoctrine()->getManager();
    //                 $remove->persist($service);
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
    //                 'error'=>"Falta campo serviceId",
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
    
    // /**
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @Route("/api/add/service/comment/", name="addServiceComment", methods={"POST"})
    //  */
    // public function addServiceComment(Request $request){
    //     $data = @json_decode($request->getContent(),true);
    //     try{
    //         if(is_array($data) && array_key_exists("serviceId",$data)){
    //             if(array_key_exists("comments",$data)){
    //                 $service = $this->getDoctrine()->getRepository(Service::class)->find($data['serviceId']);
    //                 $service->setComments($data["comments"]);
    //                 $add = $this->getDoctrine()->getManager();
    //                 $add->persist($service);
    //                 $add->flush();
    //                 $response = array(
    //                     'code'=>1,
    //                     'message'=>'Success',
    //                     'error'=>"Comentario agregado con exito",
    //                     'results'=>null
    //                 );
    //             } else{
    //                 $response = array(
    //                     'code'=>0,
    //                     'message'=>'Error',
    //                     'error'=>"Falta campo comments",
    //                     'results'=>null
    //                 );
    //              }
    //         } else{
    //             $response = array(
    //                 'code'=>0,
    //                 'message'=>'Error',
    //                 'error'=>"Falta campo serviceId",
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
