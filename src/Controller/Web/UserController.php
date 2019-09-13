<?php

namespace App\Controller\Web;


use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;


class UserController extends BaseController
{

	/**
     * @Route("/users", name="view_users")
     *
     * To List all users
     */
	public function list()
	{
		try {
			 $url = $_ENV['APP_URL']."/api/users";
			 $response = $this->processRequest($url, "GET");
			 
			 return $this->render('admin/user/listing.html.twig', ['users' => $response]);
		} catch(\Exception $e) {die($e);
			 return $this->redirectToRoute('admin_dashboard', ['error' => 'Some error occured']);
		}
	}

	/**
     * @Route("/user/create", name="create_user")
     *
     * To Create a user
     * @param Request $request
     * @param UserPasswordEncoderInterface p$asswordEncoder
     */
	public function create(Request $request, UserPasswordEncoderInterface $passwordEncoder)
	{
		
		if ($request->isMethod("GET"))  {
			return $this->render('admin/user/create.html.twig');
		} 

		try {
			$url  = $_ENV['APP_URL']."/api/users";
			$data = [
					 'name'     => $request->request->get('name'), 
					 'email'    => $request->request->get('email'), 
					 'password' => $request->request->get('password')
				    ];

			$response = $this->processRequest($url, "POST", $data);
			
			if ($response['success']) {
				return $this->redirectToRoute('view_users', ['success' => $response['message']]);
			}

			return $this->redirectToRoute('view_users', ['error' => $response['message']]);
		} catch (\Exception $e) {
			return $this->redirectToRoute('view_users', ['error' => "Some error occured"]);
    	}
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user")
     *
     * To Delete a user
     * @param Request $request
     * @param Integer $id
     */
	public function delete(Request $request, $id)
	{
		try {
			$url  = $_ENV['APP_URL']."/api/user/".$id;
			$response = $this->processRequest($url, "DELETE");
			
			if ($response['success']) {
				return $this->redirectToRoute('view_users', ['success' => $response['message']]);
			}

			return $this->redirectToRoute('view_users', ['error' => $response['message']]);
		} catch (\Exception $e) {die($e);
			return $this->redirectToRoute('view_users', ['success' => "Some error occured"]);
    	}

	}

	/**
     * @Route("/user/{id}/assign-group", name="assign_group")
     *
     * To Assign a group to user
     * @param integer $id
     * @param Request $request
     */
	public function assignGroup($id, Request $request)
	{
		if ($request->isMethod("GET"))  {
			$url      = $_ENV['APP_URL']."/api/groups/?user_id=". $id;
			$response = $this->processRequest($url, "GET");

			return $this->render('admin/user/assign-group.html.twig', ['groups' => $response['groups'], 'user' =>  $response['user']['name']]);
		} 

		try {
			$url  = $_ENV['APP_URL']."/api/user/".$id."/groups";
			$data = ['group' => $request->request->get('group')];

			$response = $this->processRequest($url, "POST", $data);

			if ($response['success']) {
				return $this->redirectToRoute('view_user_groups', ['id' => $id, 'success' => $response['message']]);
			}

			return $this->redirectToRoute('view_user_groups', ['id' => $id, 'error' => $response['message']]);
		} catch (\Exception $e) {
			return $this->redirectToRoute('view_user_groups', ['id' => $id, 'error' => "Some error occured"]);
		}
	}

	/**
     * @Route("/user/{id}/groups", name="view_user_groups")
     *
     * To List all user groups
     * @param integer $id
     */
	public function viewGroups($id)
	{
		$url      = $_ENV['APP_URL']."/api/user/". $id. "/groups";
		$response  = $this->processRequest($url, "GET");
		
		return $this->render('admin/user/group-listing.html.twig', ['results' => $response]);
	}

	/**
     * @Route("/user/{id}/remove-group/{groupId}", name="remove_group")
     *
     * To remove a group to user
     * @param integer $id
     * @param integer $groupId
     * @param Request $request
     */
	public function removeGroup($id, $groupId, Request $request)
	{
		try {
			$url      = $_ENV['APP_URL']."/api/user/". $id ."/group/". $groupId ;
			$response  = $this->processRequest($url, "DELETE");

			if ($response['success']) {
				return $this->redirectToRoute('view_user_groups', ['id' => $id, 'groupId' => $groupId , 'success' => $response['message']]);
			}

			return $this->redirectToRoute('view_user_groups', ['id' => $id, 'groupId' => $groupId , 'error' => $response['error']]);
		} catch (\Exception $e) {
			return $this->redirectToRoute('view_user_groups', ['id' => $id, 'groupId' => $groupId , 'error' => "Some error occured"]);
		}
	}

	
}