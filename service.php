<?php

use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;
use Framework\Crawler;

class Service
{
	/**
	 * Get the verse and prayer for today
	 *
	 * @param \Apretaste\Request $request
	 * @param \Apretaste\Response $response
	 *
	 * @throws \Framework\Alert
	 */
	public function _main(Request $request, Response &$response)
	{
		// get content from cache
		$content = self::loadCache();

		// crawl the data from the web
		if ($content === null) {
			// create a crawler
			Crawler::start('https://www.plough.com/es/suscribir/oracion-diaria');

			// search for result
			$base = Crawler::filter('.post-content p');
			$verse = ($base->count() > 0) ? $base->eq(0)->text() :'';
			$prayer = ($base->count() > 1) ? $base->eq(1)->html() :'';
			$prayer = strip_tags($prayer);
			$date = Crawler::filter('.post-date');
			$date = $date->count() > 0 ? $date->text() :'';
			$date = explode(',', $date)[1];

			// create a json object to send to the template
			$content = [
					'verse' => (string) $verse,
					'prayer' => $prayer,
					'date' => $date
			];

			// create the cache
			self::saveCache($content);
		}

		// send data to the view
		$response->setCache('day');
		$response->setTemplate('main.ejs', $content);

		// challenges
		Challenges::complete('oracion', $request->person->id);
	}

	/**
	 * Get cache file name
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function getCacheFileName($name): string
	{
		return TEMP_PATH.'cache/oracion_'.$name.'_'.date('Ymd').'.tmp';
	}

	/**
	 * Load cache
	 *
	 * @param $name
	 * @param null $cacheFile
	 *
	 * @return bool|mixed
	 */
	public static function loadCache($name = 'cache', &$cacheFile = null)
	{
		$data = null;
		$cacheFile = self::getCacheFileName($name);
		if (file_exists($cacheFile)) {
			$data = unserialize(file_get_contents($cacheFile));
		}
		return $data;
	}

	/**
	 * Save cache
	 *
	 * @param $name
	 * @param $data
	 * @param null $cacheFile
	 */
	public static function saveCache($data, $name = 'cache', &$cacheFile = null)
	{
		$cacheFile = self::getCacheFileName($name);
		file_put_contents($cacheFile, serialize($data));
	}
}
