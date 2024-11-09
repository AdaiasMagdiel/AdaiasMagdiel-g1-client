<?php

namespace AdaiasMagdiel\G1\Response;

class Item
{
	public string $url;

	public string $id;
	public string $feedId;
	public string $type;
	public string $created;
	public string $modified;
	public bool $isPublishing;

	public array $images;

	public array $chapeu;
	public string $section;

	public string $title;
	public string $recommendationTitle;

	public string $summary;
	public string $recommendationSummary;

	function __construct(array $data)
	{
		foreach ($data as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}

		foreach ($data["content"] as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}

		$this->images = (($data["content"] ?? [])["image"] ?? [])["sizes"] ?? [];
	}
}

class Ultimas
{
	public string $id;
	public int $nextPage;
	/** @var Item[] */
	public array $items;

	function __construct(array $data)
	{
		$this->id = $data["id"];
		$this->nextPage = $data["nextPage"];

		foreach ($data["items"] as $item) {
			$this->items[] = new Item($item);
		}
	}
}
