<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;

class UserInterestsController extends AbstractController
{

    public function __construct(JWSProviderInterface $jwsProvider) {
        $this->jwsProvider = $jwsProvider;
    }

    // /**
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @Route("/user/{id}/interests/", name="userInterests", methods="GET")
    //  */
    // public function userInterests(Request $request, $id){
    //     $token = $this->checkToken($request->headers->get('token'));
    //     try{
    //         if($token['active']){
    //             $user = $this->getDoctrine()->getRepository(User::class)->get($id);
    //             $userInterests = $this->get('serializer')->serialize($user->getUserInterests(),'json',[]);
    //             $response = array(
    //                 'code'=>1,
    //                 'message'=>"Success",
    //                 'error'=>null,
    //                 'results'=>json_decode($userInterests)
    //             );
    //         } else{
    //             $response = array(
    //                 'code'=>-1,
    //                 'message'=>"Error",
    //                 'error'=>"No access",
    //                 'results'=>null
    //             );
    //         }
    //     } catch(\Throwable $th){
    //         $response = array(
    //             'code'=>0,
    //             'message'=>"Error",
    //             'error'=>$th->getMessage(),
    //             'results'=>null
    //         );
    //     }
    //     return new JsonResponse($response, Response::HTTP_CREATED);
    // }

    // public function checkToken($token){
    //     if($token){
    //         $result = $this->get('serializer')->serialize($this->jwsProvider->load($token),'json',[]);
    //         $result = @json_decode($result,true);
    //         return array(
    //             'data'=> $result['payload'],
    //             'active' => !$result['expired']
    //         );
    //     } else{
    //         return array(
    //             'data'=> null,
    //             'active' => false
    //         );
    //     }
    // }
}
