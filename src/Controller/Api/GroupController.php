<?php

namespace App\Controller\Api;

use App\Entity\Group;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends BaseController
{

	/**
     * @Rest\Get("/groups")
     *
     * To List all groups
     * @param Request $request
     * @return JSON
     */
	public function list(Request $request)
	{
		if (!$this->authorizeRequest($request)) {
            return View::create(["success" => false, "message" => "You are not authorize to access this api"], Response::HTTP_OK);
        }

        $userId    = $request->query->get('user_id', null);
        $groupRepo = $this->getDoctrine()->getRepository(GROUP::class);
        $userRepo  = $this->getDoctrine()->getRepository(USER::class);

        if ($userId == null) {
		    $response['groups'] = $groupRepo->findAllName();
        } else {
            $groupIds = [];
            $user = $userRepo->find($userId);
            foreach ($user->getGroups() as $group) {
                $groupIds[] = $group->getId();
            }

            $response['groups'] = $groupRepo->findAllNameExcluding($groupIds);
            $response['user']   = ['id' => $user->getId(), 'name' => $user->getName()];
        }

		return View::create($response, Response::HTTP_OK);
	}

	/**
     * @Rest\Post("/groups")
     *
     * To Create a group
     * @param Request $request
     * @return JSON
     */
	public function create(Request $request)
	{
		
		if (!$this->authorizeRequest($request)) {
            return View::create(["success" => false, "message" => "You are not authorize to access this api"], Response::HTTP_OK);
        }

        try {
            $name = $request->request->get('name');
            
            $errorMsg = [];
            $name     == null ? $errorMsg[]  = "Name cannot be empty," : '';
            if (!empty($errorMsg)) {
                return View::create(["success" => false, "message" => $errorMsg], Response::HTTP_OK);
            }

			$group = new Group();
			$group->setName($request->request->get('name'));

			$this->saveEntity($group);

			return View::create(["success" => true, "message" => "Group Created successfully"], Response::HTTP_OK);
     	} catch (\Exception $e) {
     		return View::create(["success" => false, "message" => "Some error occured"], Response::HTTP_OK);
     	}
    }

    /**
     * @Rest\Delete("/group/{id}")
     *
     * To delete a group
     * @param Request $request
     * @param Integer $id
     * @return JSON
     */
    public function delete(Request $request, $id) 
    {
    	try {
            if (!$this->authorizeRequest($request)) {
                return View::create(["success" => false, "message" => "You are not authorize to access this api"], Response::HTTP_OK);
            }

    		$groupRepo  = $this->getDoctrine()->getRepository(Group::class);
    		$group      = $groupRepo->find($id);
    		
            $emptyGroup = true;
            foreach ($group->getUsers() as $user) {
                $emptyGroup = false;
                break;
            }

            if (!$emptyGroup) {
                return View::create(["success" => false, "message" => "Since some users belongs to this group so cannot delete it"], Response::HTTP_OK);
            }

    		$this->removeEntity($group);

    		return View::create(["success" => true, "message" => "Group Deleted successfully"], Response::HTTP_OK);
    	} catch(\Exception $e) {
            return View::create(["success" => false, "message" => "Some error occured"], Response::HTTP_OK);
    	}
    }
}