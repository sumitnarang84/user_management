<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use App\Helper;


class UserController extends BaseController
{

	/**
     * @Rest\Get("/users")
     *
     * To List all users
     * @param Request $request
     * @return JSON
     */
	public function list(Request $request) : View
	{
		

		$userRepo  = $this->getDoctrine()->getRepository(User::class);
		$users 	   = $userRepo->findAllNameAndEmail();

		return View::create($users, Response::HTTP_OK);
	}

	/**
     * @Rest\Post("/users")
     *
     * To Create a user
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JSON
     */
	public function create(Request $request, UserPasswordEncoderInterface $passwordEncoder)
	{
		if (!$this->authorizeRequest($request)) {
			return View::create(["success" => false, "message" => "You are not authorize to access this api"], Response::HTTP_OK);
		}

		try {
			$name  	  = $request->request->get('name', null);
			$email    = $request->request->get('email', null);
			$password = $request->request->get('password', null);

			$errorMsg = [];
			$name == null ? $errorMsg[]  = "Name cannot be empty," : '';
			$email == null ? $errorMsg[] = "Email cannot be empty," : '';
			$password == null ? $errorMsg[] = "Password cannot be empty" : '';

			
			if (!empty($errorMsg)) {
				return View::create(["success" => false, "message" => $errorMsg], Response::HTTP_OK);
			}
			
			$user = new User();
			$user->setName($name);
			$user->setEmail($email);
			$user->setPassword(
	                $passwordEncoder->encodePassword(
	                    $user,
	                    $password
	                )
	            );
			$user->setIsAdmin(false);

			//Generating and saving API key for user
			$token = Helper::generateToken();
			$user->setApiKey(hash('sha256', $token));

			$this->saveEntity($user);

			return View::create(["success" => true, "message" => "User Created successfully"], Response::HTTP_OK);

    	} catch (\Exception $e) {
    		if ($e instanceOf UniqueConstraintViolationException) {
    			return View::create(["success" => false, "message" => "Email Id Already exists"], Response::HTTP_OK);
    		} dd($e);

    		return View::create(["success" => false, "message" => "Some error occured"], Response::HTTP_OK);
    		
    	}
    }

    /**
     * @Rest\Delete("/user/{id}")
     *
     * To delete a user
     * @param Request $request
     * @param Integer $id
     * @return JSON
     */
    public function delete(Request $request, $id) 
    {
    	if (!$this->authorizeRequest($request)) {
			return View::create(["success" => false, "message" => "You are not authorize to access this api"], Response::HTTP_OK);
		}

    	try {
    		$userRepo  = $this->getDoctrine()->getRepository(User::class);
    		$user      = $userRepo->find($id);
    		
    		if ($user->hasRoleAdmin()) {
    			return View::create(["success" => false, "message" => "Admin cannot be deleted"], Response::HTTP_OK);
    		}

    		$this->removeEntity($user);

    		return View::create(["success" => true, "message" => "User Deleted successfully"], Response::HTTP_OK);
    	} catch(\Exception $e) {
    		return View::create(["success" => false, "message" => "Some error occured"], Response::HTTP_OK);
    	}
    }

	/**
     * @Rest\Post("/user/{id}/groups")
     *
     * To Assign a group to user
     * @param integer $id
     * @param Request $request
     * @return JSON
     */
	public function assignGroup($id, Request $request)
	{
		if (!$this->authorizeRequest($request)) {
			return View::create(["success" => false, "message" => "You are not authorize to access this api"], Response::HTTP_OK);
		}

		$userRepo  = $this->getDoctrine()->getRepository(User::class);
		$groupRepo = $this->getDoctrine()->getRepository(Group::class);

		try {
			$group = $groupRepo->find($request->request->get('group'));
	        $user  = $userRepo->find($id);
	        $user->addGroup($group);

			$this->saveEntity($user);

			return View::create(["success" => true, "message" => "Group assigned successfully"], Response::HTTP_OK);
		} catch (\Exception $e) {
			return View::create(["success" => false, "message" => "Some error occured"], Response::HTTP_OK);
		}
	}

	/**
     * @Rest\Get("/user/{id}/groups")
     *
     * To List all groups of a user
     * @param Request $request
     * @param Integer $id
     * @return JSON
     */
	public function viewGroups(Request $request, $id)
	{
		if (!$this->authorizeRequest($request)) {
			return View::create(["success" => false, "message" => "You are not authorize to access this api"], Response::HTTP_OK);
		}

		try {
			$userRepo  = $this->getDoctrine()->getRepository(User::class);
			$user 	   = $userRepo->find($id);

			$response['user'] = ['id' => $user->getId(), 'name' => $user->getName()];

			$i = 0;
			$response['groups'] = [];
			foreach ($user->getGroups() as $group) {
				$response['groups'][$i]['name'] = $group->getName();
				$response['groups'][$i]['id']   = $group->getId();
				$i++;
			}

			return View::create($response, Response::HTTP_OK);
		} catch (\Exception $e) {
			dd($e);
			return View::create(["success" => false, "message" => "Some error occured"], Response::HTTP_OK);
		}

	}

	/**
     * @Rest\Delete("/user/{id}/group/{groupId}")
     *
     * To remove a group to user
     * @param integer $id
     * @param integer $groupId
     * @param Request $request
     * @return JSON
     */
	public function removeGroup($id, $groupId, Request $request)
	{
		if (!$this->authorizeRequest($request)) {
			return View::create(["success" => false, "message" => "You are not authorize to access this api"], Response::HTTP_OK);
		}

		try {
			$userRepo  = $this->getDoctrine()->getRepository(User::class);
			$groupRepo = $this->getDoctrine()->getRepository(Group::class);

			$group = $groupRepo->find($groupId);
	        $user  = $userRepo->find($id);
	        $user->removeGroup($group);

			$this->saveEntity($user);

			return View::create(["success" => true, "message" => "Group removed successfully"], Response::HTTP_OK);
		} catch (\Exception $e) {
			return View::create(["success" => false, "message" => "Some error occured"], Response::HTTP_OK);
		}
	}

	
}