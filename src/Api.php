<?php

namespace AdaiasMagdiel\G1;

use Exception;
use JsonException;

use GuzzleHttp\Client;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDomBlank;

use AdaiasMagdiel\G1\Enum\Estado;
use AdaiasMagdiel\G1\Response\Ultimas;

class Api
{
	private HtmlDomParser $DOM;
	private Client $client;
	private string $lastUrl = "";
	private array $resourcesUrls = [];

	public function __construct()
	{
		$this->client = new Client();
	}

	public function ultimas(int $page = 1, ?Estado $estado = null): Ultimas
	{
		$url = "https://g1.globo.com/ultimas-noticias/";

		$this->getDOM($url);
		$this->lastUrl = $url;

		$resourceUrl = $this->getPageResourceUri($url);
		$this->resourcesUrls[$url] = $resourceUrl;

		$internalUrl = "$resourceUrl/page/$page";

		$res = $this->client->get($internalUrl, options: [
			"headers" => [
				"accept" => "*/*"
			]
		]);
		$body = $res->getBody()->getContents();

		try {
			$json = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);
		} catch (JsonException) {
			throw new Exception("Não foi possível obter as últimas notícias.");
		}

		return new Ultimas($json);
	}

	private function getDOM(string $url): HtmlDomParser
	{
		if ($this->lastUrl === $url && $this->DOM)
			return $this->DOM;

		$res = $this->client->get($url);
		$html = $res->getBody()->getContents();

		$this->DOM = HtmlDomParser::str_get_html($html);
		return $this->DOM;
	}

	private function getScripts()
	{
		$idx = 0;
		$script = $this->DOM->find("script", $idx);

		while (!($script instanceof SimpleHtmlDomBlank)) {
			yield $script;
			$script = $this->DOM->find("script", ++$idx);
		}
	}

	private function getPageResourceUri(string $url = ""): ?string
	{
		if (isset($this->resourcesUrls[$url]))
			return $this->resourcesUrls[$url];

		$sep = 'SETTINGS.BASTIAN["RESOURCE_URI"]="';

		foreach ($this->getScripts($this->DOM) as $script) {
			$text = $script->innerText();

			if (!str_contains($text, $sep)) continue;

			$resourceUri = explode($sep, $text, 2);
			$resourceUri = end($resourceUri);
			$resourceUri = explode('"', $resourceUri, 2)[0];

			return $resourceUri;
		}

		return null;
	}
}
