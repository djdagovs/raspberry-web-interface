<?php

namespace AppBundle\Network;

class ConfigurationReader
{
	/**
	 * @var string
	 */
	private $configuration;

	public function __construct($configuration)
	{
		$this->configuration = $configuration;
	}

	/**
	 * Reads the configuration string and returns the matched value(s).
	 *
	 * @param string $expression The regular expression that matches the value.
	 * @return string|array Returns the matched value as a string, or an array when there are more than 1 matches.
	 */
	public function read($expression)
	{
		if (preg_match($expression, $this->configuration, $result)) {
			if (count($result) == 2) {
				return $result[1];
			}

			return $result;
		}

		return null;
	}
}
