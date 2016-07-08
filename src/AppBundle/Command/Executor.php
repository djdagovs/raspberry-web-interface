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
	 * @var [type]
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
		if (!$this->pretend) {
			$command = new Command($command);
			$command->execute();

			$this->logger->info('Executed command: '.$command->getCommand().'. Return status: '.$command->getReturnStatus());
		} else {
			$this->logger->info('[Pretend] Execute command: '.$command.'.');
		}

		return $command;
	}
}
