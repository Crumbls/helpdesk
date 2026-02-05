<?php

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	protected function buildResponse(array $data, Request $request, int $statusCode = 200)
	{
		$preferred = $request->prefers(['application/json', 'application/xml']);

		if ($preferred === 'application/xml') {
			return $this->xmlResponse($data, $statusCode);
		}

		return response()->json($data, $statusCode);
	}

	private function xmlResponse(array $data, int $status = 200)
	{
		$xml = new \SimpleXMLElement('<response/>');

		$this->arrayToXml($data, $xml);

		return response($xml->asXML(), $status)
			->header('Content-Type', 'application/xml');
	}

	private function arrayToXml(array $data, \SimpleXMLElement $xml): void
	{
		foreach ($data as $key => $value) {
			$elementName = is_numeric($key) ? 'item' : $key;

			if (is_array($value)) {
				$child = $xml->addChild($elementName);
				$this->arrayToXml($value, $child);
			} else {
				$xml->addChild($elementName, htmlspecialchars((string) ($value ?? '')));
			}
		}
	}

}
