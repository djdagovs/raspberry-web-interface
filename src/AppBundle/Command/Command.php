<?php

namespace AppBundle\Command;

use AppBundle\Exception\CommandNotValidException;
use AppBundle\Exception\CommandNotExecutedException;

class Command
{
	/**
	 * @var string
	 */
	private $command;

	/**
	 * @var array
	 */
	private $output;

	/**
	 * @var int
	 */
	private $returnStatus;

	public function __construct($command)
	{
		$this->setCommand($command);
	}

	/**
	 * Returns the command to execute.
	 *
	 * @return string The command to execute.
	 */
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 * Sets the command to execute.
	 *
	 * @param string $command The command to execute.
	 * @throws CommandNotValidException if the command supplied is not type of 'string'.
	 */
	public function setCommand($command)
	{
		if (!is_string($command)) {
			throw new CommandNotValidException('The command may only be a string.');
		}

		$this->command = $command;
	}

	/**
	 * Returns the output of the executed command.
	 *
	 * @param $offset int The array output offset.
	 * @param $length int The max length of the output array, null for unlimited.
	 * @return array The output of the executed command.
	 * @throws CommandNotExecutedException if the command is not yet executed.
	 */
	public function getOutput($offset = 0, $length = null)
	{
		if (is_null($this->output)) {
			throw new CommandNotExecutedException('Please execute the command first before retrieving the output.');
		}

		return array_slice($this->output, $offset, $length);
	}

	/**
	 * Returns the number of output rows of the executed command.
	 *
	 * @param $offset int The array output offset.
	 * @param $length int The max length of the output array, null for unlimited.
	 * @return int The number of output rows of the executed command.
	 * @throws CommandNotExecutedException if the command is not yet executed.
	 */
	public function getOutputCount($offset = 0, $length = null)
	{
		return count($this->getOutput($offset, $length));
	}

	/**
	 * Returns the return status of the command.
	 *
	 * @return int The return status of the command.
	 */
	public function getReturnStatus()
	{
		if (is_null($this->returnStatus)) {
			throw new CommandNotExecutedException('Please execute the command first before retrieving the return status.');
		}

		return $this->returnStatus;
	}

	/**
	 * Returns true if the executed command was valid, false if not.
	 *
	 * @throws CommandNotExecutedException if the command is not yet executed.
	 * @return boolean True if the executed command was valid, false if not.
	 */
	public function isValid()
	{
		return ($this->getReturnStatus() == 0);
	}

	/**
	 * Executes the command.
	 *
	 * @param bool $pretend Set to true to pretend the execution of the command.
	 * @return void
	 */
	public function execute($pretend = false)
	{
		if (!$pretend) {
			$output = array();
			$returnStatus = null;

			exec($this->command, $output, $returnStatus);

			$this->output = $output;
			$this->returnStatus = $returnStatus;
		} else {
			$this->output = array();
			$this->returnStatus = 0;
		}
	}
}
