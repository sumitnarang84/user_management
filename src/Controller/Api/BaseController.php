<?php
namespace App\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use Defuse\Crypto\Crypto;
use App\Entity\User;

class BaseController extends FOSRestController
{

	/**
	* To save the entity in DB via entity Manager
	* @param Entity $entity
	* @return null
	*/
	public function saveEntity($entity)
	{
		$entityManager = $this->getDoctrine()->getManager();
	    $entityManager->persist($entity);
	    $entityManager->flush();
	}

	/**
	* To remove the entity in DB via entity Manager
	* @param Entity $entity
	* @return null
	*/
	public function removeEntity($entity)
	{
		$entityManager = $this->getDoctrine()->getManager();
	    $entityManager->remove($entity);
	    $entityManager->flush();
	}

	/**
	* To Authorize api request to check wether it belongs to a valid user
	* @param Request $request
	* @return boolean
	*/
	public function authorizeRequest($request)
	{
		$headers   = $request->headers->all();
		$authUser  = $headers['php-auth-user'][0];
		$authPass  = $headers['php-auth-pw'][0];

		$username  = Crypto::decryptWithPassword($authUser, $_ENV['APP_SECRET']);
		$apiKey    = $authPass;
		$userRepo  = $this->getDoctrine()->getRepository(User::class);
		$user 	   = $userRepo->findOneBy(['email' => $username, 'api_key' => $apiKey]);

		if ($user == null || !in_array('ROLE_ADMIN', $user->getRoles())) {
			return false;
		}

		return true;
	}
	
}