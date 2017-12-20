<?php

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

class Oracion extends Service
{

	public $client = null;
	/**
	 * Crawler client
	 *
	 * @return \Goutte\Client
	 */
	public function getClient()
	{
		if (is_null($this->client))
		{
			$this->client = new Client();
			$guzzle = new GuzzleClient(["verify" => false]);
			$this->client->setClient($guzzle);
		}
		return $this->client;
	}

	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		// create a new client
		$client = $this->getClient();

		// create a crawler
		$crawler = $client->request('GET', "https://www.plough.com/es/suscribir/oracion-diaria");

		// search for result
		$base = $crawler->filter('.post-content p');

		$verse = ($base->count() > 0) ? $base->eq(0)->text() : "";
		$prayer = ($base->count() > 1) ? $base->eq(1)->html() : "";
		$date = $crawler->filter('.post-date');
		$date = $date->count() > 0 ? $date->text() : "";

		if ($verse == "" && $prayer == "")
			return new Response();

		// create a json object to send to the template
		$responseContent = array(
			"verse" => $verse,
			"prayer" => $prayer,
			"date" => $date
		);

		// create the response
		$response = new Response();
		$response->setCache("day");
		$response->setResponseSubject("Oracion del dia");
		$response->createFromTemplate("basic.tpl", $responseContent);
		return $response;
	}
}
