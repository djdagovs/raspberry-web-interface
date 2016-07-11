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

    public function __construct(NetworkInterface $interface, LoggerInterface $logger, Executor $commandExecutor)
    {
        $this->interface = $interface;
        $this->logger = $logger;
        $this->commandExecutor = $commandExecutor;
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
            preg_match('/Selected interface \'([a-z0-9]+)\'/i', $output, $result);

            $selectedInterface = isset($result[1]) ? $result[1] : 'Unknown';

            $this->logger->info('Scanning networks... Selected interface: "'.$selectedInterface.'"');

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
            sleep(3);
        }

        $command = $this->commandExecutor->execute('wpa_cli -i '.$this->interface->getName().' scan_results');
        $results = array();

        if ($command->isValid()) {
            $output = $command->getOutput();

            // Remove comment rows
            for ($i = 0; $i < 1; $i++) {
                array_shift($output);
            }

            foreach ($output as $row) {
                $result = new Result($row);
                $results[] = $result->getDetails();
            }

            $this->logger->info('Found '.$command->getOutputCount().' networks.');
        }

        return $results;
    }
}
