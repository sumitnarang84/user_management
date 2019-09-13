<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class DashboardController extends AbstractController
{

	/**
     * @Route("/dashboard", name="admin_dashboard")
     */
	public function dashboard()
	{
		return $this->render('admin/dashboard.html.twig');
	}
}