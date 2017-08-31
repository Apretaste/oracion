<?php

use Goutte\Client; // UNCOMMENT TO USE THE CRAWLER OR DELETE

class Oracion extends Service
{
	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		// create a new client
		$client = new Client();
		$guzzle = $client->getClient();
		$guzzle->setDefaultOption('verify', false);
		$client->setClient($guzzle);

		// create a crawler
		$crawler = $client->request('GET', "http://www.plough.com/es/suscribir/oracion-diaria");

		// search for result
		$base = $crawler->filter('.post-content p');
		$verse = $base->eq(0)->text();
		$prayer = $base->eq(1)->html();
		$date = $crawler->filter('.post-date')->text();

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
