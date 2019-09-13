<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Defuse\Crypto\Crypto;


class BaseController extends AbstractController
{

	/**
	* To Process HTTP Request
	*
	* @param String $url
	* @param String $method
	* @param Array $data
	* @return Array
	*/
	public function processRequest($url, $method, $data = [])
	{
		
		try {
			$user 			   = $this->getUser();
			$encryptedUserName = Crypto::encryptWithPassword($user->getEmail(), $_ENV['APP_SECRET']);
			
			$client = HttpClient::create([
				'auth_basic' => [$encryptedUserName, $user->getApiKey()],
			]);

			if (count($data) == 0) {
				$response = $client->request($method, $url, []);
			} else {
				$response = $client->request($method, $url, ['body' => $data]);
			}

			$content = $response->getContent();
			
			return json_decode($content, true);
		} catch (\Exception $e) {dd($e);
			throw new \Exception("Unable to process the request");
		}
	}

}