<?php

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Service
{
	/**
	 * Get the verse and prayer for today
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request, Response &$response)
	{
		// get content from cache
		$cache = Utils::getTempDir() . "oracion" . date("Ymd") . ".cache";
		if (file_exists($cache)) {
			$content = unserialize(file_get_contents($cache));
		}

		// crawl the data from the web
		else {
			// create a crawler
			$client = new Client();
			$crawler = $client->request('GET', "https://www.plough.com/es/suscribir/oracion-diaria");

			// search for result
			$base = $crawler->filter('.post-content p');
			$verse = ($base->count() > 0) ? $base->eq(0)->text() : "";
			$prayer = ($base->count() > 1) ? $base->eq(1)->html() : "";
			$prayer = strip_tags($prayer);
			$date = $crawler->filter('.post-date');
			$date = $date->count() > 0 ? $date->text() : "";
			$date = explode(',', $date)[1];

			// create a json object to send to the template
			$content = [
				"verse"  => (string) $verse,
				"prayer" => $prayer,
				"date"   => $date
			];

			// create the cache
			file_put_contents($cache, serialize($content));
		}

		// send data to the view
		$response->setCache("day");
		$response->setTemplate("main.ejs", $content);

		Challenges::complete('oracion', $request->person->id);
	}
}
