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

	/**
	 * @var boolean
	 */
	private $isValid;

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
	 * @throws CommandNotExecutedException if the command is not yet executed.
	 * @return array The output of the executed command.
	 */
	public function getOutput()
	{
		if($this->output == null) {
			throw new CommandNotExecutedException('Please execute the command first before retrieving the output.');
		}

		return $this->output;
	}

	/**
	 * Returns the return status of the command.
	 *
	 * @return int The return status of the command.
	 */
	public function getReturnStatus()
	{
		if($this->returnStatus == null) {
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
		if($this->isValid == null) {
			throw new CommandNotExecutedException('Please execute the command first before checking the validity.');
		}

		return $this->isValid;
	}

	/**
	 * Executes the command.
	 *
	 * @return void
	 */
	public function execute()
	{
		$output = array();
		$returnStatus = null;

		exec($this->command, $output, $returnStatus);

		$this->output = $output;
		$this->returnStatus = $returnStatus;
	}
}
