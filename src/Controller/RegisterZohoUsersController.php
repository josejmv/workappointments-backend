<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\User;

class RegisterZohoUsersController extends AbstractController
{

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/api/zoho/init/", name="zohoInit", methods={"GET"})
     */
    public function zohoInit(Request $request){
        return new JsonResponse(["code" => 1,"message" => "Success", "error" => null, "response" => $_GET], Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/api/add/zoho/operators/", name="addZohoOperators", methods={"POST"})
     */
    public function addZohoOperators(Request $request){
        try{
            $token = $request->headers->get('token');
            $page = 1;
            if($token){
                $data = $this->getZohoOperators($token, $page);
                if(is_array($data) && !empty($data) && array_key_exists("data",$data))
                    $zohoOperators = $data["data"];
                else 
                    $zohoOperators = [];
            }
            else{
                return new JsonResponse(["code" => 0, "message" => "Token no definido", "response" => null], Response::HTTP_CREATED);
            }
            while(!empty($zohoOperators)){
                foreach($zohoOperators as $user){
                    $verify = $this->getDoctrine()->getRepository(User::class)->findOneBy(["email" => $user["Email"]]);
                    if(!($verify instanceof User))
                        $this->getDoctrine()->getRepository(User::class)->saveZohoOperators($user);
                }
                $page++;
                $data = $this->getZohoOperators($token, $page);
                if(is_array($data) && !empty($data) && array_key_exists("data",$data))
                    $zohoOperators = $data["data"];
                else
                    $zohoOperators = [];
            }
            return new JsonResponse(["code" => 1,"message" => "Success", "error" => null,"response" => null], Response::HTTP_CREATED);
        }catch(\Throwable $error){
            return new JsonResponse(["code" => 0, "message" => "Error", "error" => $error->getMessage(), "response" => null], Response::HTTP_CREATED);
        }
    }

    public function getZohoOperators($token, $page){
        try{
            $httpClient = HttpClient::create();
            $zohoOperators = $httpClient->request(
                'GET',
                'https://www.zohoapis.com/crm/v2/Operarios_para_Entrevista?page='.$page,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => $token,
                    ]
                ]
            );
            return $zohoOperators->toArray();
        }catch(\Throwable $error){
            return $error->getMessage();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/api/add/zoho/managers/", name="addZohoManagers", methods={"POST"})
     */
    public function addZohoManagers(Request $request){
        try{
            $token = $request->headers->get('token');
            $page = 1;
            if($token){
                $data = $this->getZohoManagers($token, $page);
                if(is_array($data) && !empty($data) && array_key_exists("users",$data))
                    $zohoManagers = $data["users"];
                else 
                    $zohoManagers = [];
            }
            while(!empty($zohoManagers)){
                foreach($zohoManagers as $user){
                    $verify = $this->getDoctrine()->getRepository(User::class)->findOneBy(["email" => $user["email"]]);
                    if(!($verify instanceof User)){
                        if($user['profile']['name'] == "Administrator"){
                            $this->getDoctrine()->getRepository(User::class)->saveZohoAdmin($user);
                        } else{
                            $this->getDoctrine()->getRepository(User::class)->saveZohoManagers($user);
                        }
                    }
                }
                $page++;
                $data = $this->getZohoManagers($token, $page);
                if(is_array($data) && !empty($data) && array_key_exists("users",$data))
                    $zohoManagers = $data["users"];
                else 
                    $zohoManagers = [];
            }
            return new JsonResponse(["code" => 1,"message" => "success","error" => null, "response" => null], Response::HTTP_CREATED);
        }catch(\Throwable $error){
            return new JsonResponse(["code" => 0, "message" => "Error", "error" => $error->getMessage(), "response" => null], Response::HTTP_CREATED);
        }
    }

    public function getZohoManagers($token,$page){
        try{
            $httpClient = HttpClient::create();
            $zohoManagers = $httpClient->request(
                'GET',
                'https://www.zohoapis.com/crm/v2/users?type=AllUsers&page='.$page,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => $token,
                    ]
                ]
            );
            return $zohoManagers->toArray();
        }catch(\Throwable $error){
            return $error->getMessage();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/api/refresh/accessToken/", name="refreshToken", methods={"POST"})
     */
    public function refreshToken(Request $request){
        try{
            $newToken = $this->refresh();
            return new JsonResponse(["code" => 1,"message" => "success", "error" => null, "response" => $newToken], Response::HTTP_CREATED);
        }catch(\Throwable $error){
            return new JsonResponse(["code" => 0, "message" => "Error", "error" => $error->getMessage(), "response" => null], Response::HTTP_CREATED);
        }
    }

    public function refresh(){
        try{
            $httpClient = HttpClient::create();
            $zohoUsers = $httpClient->request(
                'POST',
                'https://accounts.zoho.com/oauth/v2/token?refresh_token=1000.94bcaf3282b9fc3b331f80998dd3b6f0.b955b6ebd43d0e3deb4478c3efc042d4&client_id=1000.4JLA1LH23WSYEGC8JD1LHEHG0K46VR&client_secret=6c84e80badabacb5cee925d8e4ad251f91e090ed81&grant_type=refresh_token',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]
            );
            return $zohoUsers->toArray();
        }catch(\Throwable $error){
            return $error->getMessage();
        }
    }
}
