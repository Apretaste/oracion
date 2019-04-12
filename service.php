<?php

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Service
{

	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request, Response &$response)
	{
		// create a new client
		$client = new Client();
		
		// create a crawler
		$crawler = $client->request('GET', "https://www.plough.com/es/suscribir/oracion-diaria");
	
		// search for result
		$base = $crawler->filter('.post-content p');
		$verse = ($base->count() > 0) ? $base->eq(0)->text() : "";
		$prayer = ($base->count() > 1) ? $base->eq(1)->html() : "";
		$prayer = strip_tags($prayer);
		$date = $crawler->filter('.post-date');
		$date = $date->count() > 0 ? $date->text() : "";
		$date = explode(',',$date)[1];
		$imageObj = $crawler->filter('.post .post-content > img');
		if ($imageObj->count() != 0) {
			$imgUrl = trim($imageObj->attr("src"));
			$imgAlt = trim($imageObj->attr("alt"));
	  
			// get the image
			if (!empty($imgUrl)) {
			  $imgUrl = explode('?',$imgUrl)[0];
			  $imgName = Utils::generateRandomHash() . "." . pathinfo($imgUrl, PATHINFO_EXTENSION);
			  $img     = \Phalcon\DI\FactoryDefault::getDefault()->get('path')['root'] . "/temp/$imgName";
			  file_put_contents($img, file_get_contents($imgUrl));
			}
		  }
		//get images
		$pathToService = Utils::getPathToService($response->serviceName);
		$img2 = "$pathToService/images/background.jpg";

		// create a json object to send to the template
		$responseContent = array(
			"verse"  => (string)$verse,
			"prayer" => $prayer,
			"date"   => $date,
			"img"    => $img,
			"imgAlt" => $imgAlt,
			"img2"   => $img2,
		);

		// get the image if exist to send to the template
		$images = [];
		if (!empty($responseContent['img'])) {
		  $images = [
			  $responseContent['img'],
			  $responseContent['img2'],
			 ];
		}else{
			$images = [
			  $responseContent['img2'],
			 ]; 
			
		}
		$response->setCache("day");
		$response->setLayout("oracion.ejs");
		$response->setTemplate("main.ejs", $responseContent, $images);
		
	}
}
