<?php

namespace AppBundle\Wireless\Scanner;

use Psr\Log\LoggerInterface;
use AppBundle\Command\Executor;
use AppBundle\Wireless\Scanner\Result;
use AppBundle\Network\NetworkInterface;

class Scanner
{
    /**
     * @var NetworkInterface
     */
    private $interface;

    /**
    * @var LoggerInterface
    */
    private $logger;

    /**
    * @var Executor
    */
    private $commandExecutor;

    /**
     * @var int
     */
    private $maxTries;

    public function __construct(NetworkInterface $interface, LoggerInterface $logger, Executor $commandExecutor, $maxTries = 3)
    {
        $this->interface = $interface;
        $this->logger = $logger;
        $this->commandExecutor = $commandExecutor;
        $this->maxTries = $maxTries;
    }

    /**
     * Scans wireless networks.
     *
     * @return bool True if the scan succeeded, false if not.
     */
    public function scan()
    {
        $command = $this->commandExecutor->execute('wpa_cli -i '.$this->interface->getName().' scan');

        if ($command->isValid()) {
            $output = implode(' ', $command->getOutput());

            $this->logger->info('Scanning networks...');

            return true;
        }

        return false;
    }

    /**
     * Scans wireless networks and returns an array with the scan results.
     *
     * @return array The scan results.
     */
    public function getResults()
    {
        if ($this->scan()) {
            sleep(2);
        }

        // Check if there are any results
        for ($i = 1; $i <= $this->maxTries; $i++) {
            // Wait before retrieving results
            sleep(1);

            $this->logger->info(sprintf('Getting wireless scan results... Attempt %d of %d.', $i , $this->maxTries));

            $command = $this->commandExecutor->execute('wpa_cli -i '.$this->interface->getName().' scan_results');

            if ($command->isValid()) {
                // Offset 1 removes the comment lines
                $count = $command->getOutputCount(1);

                // If more than one network is found during an iteration, continue.
                if ($count > 1) {
                    $this->logger->info(sprintf('Found %d networks, continuing.', $count));
                    break;
                } else {
                    $this->logger->info(sprintf('Found %d network(s), trying again...', $count));
                }
            }
        }

        $results = array();

        if ($command->isValid()) {
            // Offset 1 removes the comment lines
            $output = $command->getOutput(1);

            foreach ($output as $row) {
                $result = new Result($row);
                $results[] = $result->getDetails();
            }

            $this->logger->info('Found '.$command->getOutputCount(1).' network(s).');
        }

        return $results;
    }
}
