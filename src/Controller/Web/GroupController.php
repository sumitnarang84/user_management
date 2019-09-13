<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Group;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends BaseController
{

	/**
     * @Route("/groups", name="view_groups")
     * 
     * To list all groups
     */
	public function list()
	{
		try {
			 $url = $_ENV['APP_URL']."/api/groups";
			 $response = $this->processRequest($url, "GET");
			 
			 return $this->render('admin/group/listing.html.twig', ['groups' => $response['groups']]);
		} catch(\Exception $e) {
			 return $this->redirectToRoute('admin_dashboard', ['error' => 'Some error occured']);
		}
	}

	/**
     * @Route("/group/create", name="create_group")
     *
     * To Create a group
     * @param Request $request
     */
	public function create(Request $request)
	{
		
		if ($request->isMethod("GET"))  {
			return $this->render('admin/group/create.html.twig');
		} 

		try {
			$url  = $_ENV['APP_URL']."/api/groups";
			$data = [
					 'name'     => $request->request->get('name')
					];

			$response = $this->processRequest($url, "POST", $data);
			
			if ($response['success']) {
				return $this->redirectToRoute('view_groups', ['success' => $response['message']]);
			}

			return $this->redirectToRoute('view_groups', ['error' => $response['message']]);
		} catch (\Exception $e) {
			return $this->redirectToRoute('view_groups', ['error' => "Some error occured"]);
    	}
    }

    /**
     * @Route("/group/delete/{id}", name="delete_group")
     *
     * To Delete a group
     * @param Request $request
     * @param Integer $id
     */
	public function delete(Request $request, $id)
	{
		try {
			$url  = $_ENV['APP_URL']."/api/group/".$id;
			$response = $this->processRequest($url, "DELETE");
			
			if ($response['success']) {
				return $this->redirectToRoute('view_groups', ['success' => $response['message']]);
			}

			return $this->redirectToRoute('view_groups', ['error' => $response['message']]);
		} catch (\Exception $e) {
			return $this->redirectToRoute('view_groups', ['success' => "Some error occured"]);
    	}

	}
}