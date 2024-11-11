<?php

namespace AdaiasMagdiel\G1;

use DateTime;
use DateInterval;
use stdClass;

class Cache
{
	public const SEP = "\n-*-\n";
	public const DATETIME_FORMAT = "Y-m-d H:i:s";

	public function __construct(
		public string $path = __DIR__ . "/.g1-cache",
		public int $expires = -1
	) {
		if (!is_dir($this->path)) {
			mkdir($this->path, recursive: true);
		}
	}

	public function getFilepathFromKey(string $key): string
	{
		return rtrim($this->path, "/") . "/" . md5($key);
	}

	public function formatData(string $value, int $expires): string
	{
		$datetime = new DateTime();

		if ($expires !== -1) {
			$interval = DateInterval::createFromDateString("$expires seconds");
			$datetime->add($interval);
		}

		return  $expires                                  . $this::SEP .
			$datetime->format($this::DATETIME_FORMAT) . $this::SEP .
			$value;
	}

	public function convertData(string $value): stdClass
	{
		list($expires, $datetimeValue, $fileValue) = explode($this::SEP, $value, 3);

		$obj = new stdClass();
		$obj->expires = intval($expires);
		$obj->datetime = DateTime::createFromFormat($this::DATETIME_FORMAT, $datetimeValue);
		$obj->value = $fileValue;

		return $obj;
	}

	public function set(string $key, string $value, int $expires = -1)
	{
		if ($expires === -1) $expires = $this->expires;

		$filepath = $this->getFilepathFromKey($key);
		$data = $this->formatData($value, $expires);

		file_put_contents($filepath, $data);
	}

	public function get(string $key, callable $action, int $expires = -1): string
	{
		if ($expires === -1) $expires = $this->expires;

		$filepath = $this->getFilepathFromKey($key);

		// File not exists
		if (!file_exists($filepath)) {
			$value = $action();
			$this->set($key, $value, $expires);

			return $value;
		}

		// File value
		$rawValue = file_get_contents($filepath);

		// File exists but is empty
		if (empty(trim($rawValue))) {
			$value = $action();
			$this->set($key, $value, $expires);

			return $value;
		}

		$res = $this->convertData($rawValue);

		// File don't expires
		if ($res->expires === -1) {
			return $res->value;
		}

		// File expires
		$now = new DateTime();

		if ($now > $res->datetime) {
			$value = $action();
			$this->set($key, $value, $expires);

			return $value;
		}

		return $res->value;
	}
}
