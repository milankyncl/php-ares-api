<?php

namespace MilanKyncl;

/**
 * Class AresAPI
 *
 * @package MilanKyncl
 */

class AresAPI {

	const BACKEND_URL = 'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest';

	/**
	 * findByIN
	 * --------
	 *
	 * Find subjects by it's name.
	 *
	 * @param $in
	 *
	 * @return array|bool
	 * @throws \Exception
	 */

	public function findByIN($in) {

		$response = $this->_createRequest(self::BACKEND_URL . '/ekonomicke-subjekty/vyhledat', ['ico' => [$in]]);

		if(
			$response['pocetCelkem'] < 1
			|| empty($response['ekonomickeSubjekty'][0])
			|| $response['ekonomickeSubjekty'][0]['ico'] != $in
		)
			return false;

		$subject = $response['ekonomickeSubjekty'][0];

		return [
			'in' => $subject['ico'] ?? null,
			'tin' => $subject['dic'] ?? null,
			'name' => $subject['obchodniJmeno'] ?? null,
			'street' => trim(
				($subject['sidlo']['nazevUlice'] ?? ($subject['sidlo']['nazevCastiObce'] ?? ''))
				. ' '
				. (
					empty($subject['sidlo']['cisloOrientacni'])
					? ($subject['sidlo']['cisloDomovni'] ?? '')
					: (
						(
							isset($subject['sidlo']['cisloDomovni'])
							? $subject['sidlo']['cisloDomovni'] . '/'
							: '')
						. $subject['sidlo']['cisloOrientacni']
					)
				)
			),
			'city' => $subject['sidlo']['nazevObce'] ?? null,
			'zip' => $subject['sidlo']['psc'] ?? null
		];

	}

	/**
	 * findByName
	 * ----------
	 *
	 * Find subjects by it's name.
	 *
	 * @param $name
	 *
	 * @return array
	 * @throws \Exception
	 */

	public function findByName($name) {

		$response = $this->_createRequest(self::BACKEND_URL . '/ekonomicke-subjekty/vyhledat', ['obchodniJmeno' => $name]);

		$result = [];

		if(
			$response['pocetCelkem'] < 1
			|| empty($response['ekonomickeSubjekty'])
		)
			return $result;

		$result = [];

		foreach($response['ekonomickeSubjekty'] as $subject) {

			$result[] = [
				'in' => $subject['ico'] ?? null, // Identifikační číslo
				'name' => $subject['obchodniJmeno'] ?? null, // Jméno subjektu
				'address' => $subject['sidlo']['textovaAdresa'] ?? null // Adresa
			];
		}

		return $result;

	}

	/**
	 * _createRequest
	 * --------------
	 *
	 * Create API request, parse JSON and return array.
	 *
	 * @return array|false
	 *
	 * @throws \Exception
	 */

	private function _createRequest($url, $data) {

		$curl = curl_init($url);

		curl_setopt_array($curl, [
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				"Content-type: application/json"
			],
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_SSL_VERIFYPEER => false // TODO
		]);

		$content = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if($status != 200)
			throw new \Exception('Error: call to URL ' . $url . ' failed with status ' . $status . ', response ' . $content . ', curl_error ' . curl_error($curl) . ', curl_errno ' . curl_errno($curl));

		if(!$content)
			throw new \Exception(curl_error($curl), curl_errno($curl));

		curl_close($curl);

		/**
		 * Parse JSON now
		 */

		$json = json_decode($content, true);

		if(!empty($json))
			return $json;

		return false;

	}

}