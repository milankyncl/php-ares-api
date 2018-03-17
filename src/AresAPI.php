<?php

namespace MilanKyncl;

/**
 * Class AresAPI
 *
 * @package MilanKyncl
 */

class AresAPI {

	const BACKEND_URL = 'http://wwwinfo.mfcr.cz/cgi-bin/ares';

	public function findByName($name) {

		$xml = $this->_createRequest(self::BACKEND_URL . '/ares_es.cgi?obch_jm=' . urlencode($name));

		$ns = $xml->getDocNamespaces();
		$data = $xml->children($ns['are']);
		$subjects = $data->children($ns['dtt'])->V->S;

		$result = [];

		foreach($subjects as $subject) {

			$result[] = [
				'in' => $subject->ico, // Identifikační číslo
				'name' => $subject->ojm, // Jméno subjektu
				'address' => $subject->jmn // Adresa
			];
		}

		return $result;

	}

	/**
	 * Create API Request
	 * ------------------
	 *
	 * Create API request, parse XML feed and return array.
	 *
	 * @return \SimpleXMLElement
	 *
	 * @throws \Exception
	 */

	private function _createRequest($url) {

		$curl = curl_init($url);

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

		$xml = simplexml_load_string($content);

		if($xml)
			return $xml;

		return false;

	}

}