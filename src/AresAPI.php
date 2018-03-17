<?php

namespace MilanKyncl;

/**
 * Class AresAPI
 *
 * @package MilanKyncl
 */

class AresAPI {

	const BACKEND_URL = 'http://wwwinfo.mfcr.cz/cgi-bin/ares';

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

		$xml = $this->_createRequest('http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=' . $in);

		$ns = $xml->getDocNamespaces();
		$data = $xml->children($ns['are']);
		$el = $data->children($ns['D'])->VBAS;

		if (strval($el->ICO) == $in) {

			return [
				'in' => (string) $el->ICO,
				'tin' => (string) $el->DIC,
				'name' => (string) $el->OF,
				'street' => (string) $el->AA->NU . ' ' . (($el->AA->CO == '') ? $el->AA->CD : $el->AA->CD . '/' . $el->AA->CO),
				'city' => (string) $el->AA->N,
				'zip' => (string) $el->AA->PSC
			];
		}

		return false;
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

		$xml = $this->_createRequest(self::BACKEND_URL . '/ares_es.cgi?obch_jm=' . urlencode($name));

		$ns = $xml->getDocNamespaces();
		$data = $xml->children($ns['are']);
		$subjects = $data->children($ns['dtt'])->V->S;

		$result = [];

		foreach($subjects as $subject) {

			$result[] = [
				'in' => (string) $subject->ico, // Identifikační číslo
				'name' => (string) $subject->ojm, // Jméno subjektu
				'address' => (string) $subject->jmn // Adresa
			];
		}

		return $result;

	}

	/**
	 * _createRequest
	 * --------------
	 *
	 * Create API request, parse XML feed and return array.
	 *
	 * @return \SimpleXMLElement|false
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