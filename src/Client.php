<?php

namespace AdaiasMagdiel\G1;

use Exception;
use JsonException;

use GuzzleHttp\Client as GuzzleHttp;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDomBlank;

use AdaiasMagdiel\G1\Enum\Estado;
use AdaiasMagdiel\G1\Response\Ultimas;
use AdaiasMagdiel\G1\Cache;

class Client
{
	private HtmlDomParser $DOM;
	private GuzzleHttp $client;
	private Cache $cache;

	private string $baseUrl = "https://g1.globo.com/";

	public function __construct(string $cacheDir = __DIR__ . "/.g1-cache")
	{
		$this->client = new GuzzleHttp();
		$this->cache = new Cache(path: $cacheDir, expires: 60 * 10);
	}

	public function ultimas(int $page = 1, ?Estado $estado = null): Ultimas
	{
		$url = $this->baseUrl . ($estado ? $estado->value : "") . "/ultimas-noticias/";

		$resourceUrl = $this->getPageResourceUri($url);
		$internalUrl = "$resourceUrl/page/$page";

		$body = $this->cache->get($internalUrl, function () use ($internalUrl) {
			$res = $this->client->get($internalUrl, options: [
				"headers" => [
					"accept" => "*/*"
				]
			]);
			$body = $res->getBody()->getContents();

			return $body;
		});

		try {
			$json = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);
		} catch (JsonException) {
			throw new Exception("Não foi possível obter as últimas notícias.");
		}

		return new Ultimas($json);
	}

	private function getDOM(string $url): HtmlDomParser
	{

		$html = $this->cache->get($url, function () use ($url) {
			$res = $this->client->get($url);
			$html = $res->getBody()->getContents();
			return $html;
		});

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

	private function getPageResourceUri(string $url = ""): string
	{
		$this->getDOM($url);

		$key = "resource:$url";

		$resource = $this->cache->get($key, function () {
			$sep = 'SETTINGS.BASTIAN["RESOURCE_URI"]="';

			foreach ($this->getScripts($this->DOM) as $script) {
				$text = $script->innerText();

				if (!str_contains($text, $sep)) continue;

				$resourceUri = explode($sep, $text, 2);
				$resourceUri = end($resourceUri);
				$resourceUri = explode('"', $resourceUri, 2)[0];

				return $resourceUri;
			}

			return "";
		}, expires: 60 * 30);

		return $resource;
	}
}
