<?php

namespace AppBundle\Command;

use Psr\Log\LoggerInterface;
use AppBundle\Command\Command;

class Executor
{
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $pretend;

	public function __construct(LoggerInterface $logger, $pretend = false)
	{
		$this->logger = $logger;
		$this->pretend = $pretend;
	}

	/**
	 * Executes the given command.
	 *
	 * @param string $command The command to execute.
	 * @return Command The command object.
	 */
	public function execute($command)
	{
		$command = new Command($command);

		if ($this->pretend) {
			$this->logger->debug('The commands are not really executed, "pretend" is set to true.');
		}

		$command->execute($this->pretend);

		$this->logger->info('Executed command: '.$command->getCommand().'. Return status: '.$command->getReturnStatus());

		return $command;
	}
}
