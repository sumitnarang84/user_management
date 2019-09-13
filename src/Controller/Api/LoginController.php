<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;

class LoginController extends BaseController
{
    /**
     * @Rest\Post("/login")
     *
     * @param UserPasswordEncoderInterface passwordEncoder
     * @param Request request
     * @return Json
     */
    public function login(UserPasswordEncoderInterface $passwordEncoder, Request $request)
    {
        
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
        ];

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            return View::create(["error" => "Email does not exist"], Response::HTTP_OK);
            
        }

        if ($passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            return View::create(['api_key' => $user->getApiKey()])  ;
        } 

        return View::create(["error" => "Invalid credentials"], Response::HTTP_OK);
        
    }

   
}
