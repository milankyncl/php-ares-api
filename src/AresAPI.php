<?php

namespace MilanKyncl;

/**
 * Class AresAPI
 *
 * @package MilanKyncl
 */

class AresAPI {

	const BACKEND_URL = 'https://wwwinfo.ares.cz';

	public function findByName() {

	}

	/**
	 * Create API Request
	 * ------------------
	 *
	 * Create API request, parse XML feed and return array.
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */

	private function _createRequest($parameters) {

		$curl = curl_init(self::BACKEND_URL);

		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0
		]);

		$content = curl_exec($curl);

		if (!$content)
			throw new \Exception(curl_error($curl), curl_errno($curl));

		curl_close($curl);

		/**
		 * Parse XML now
		 */

		return $content;

	}

}