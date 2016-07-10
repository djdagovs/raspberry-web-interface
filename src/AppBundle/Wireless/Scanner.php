<?php

namespace AppBundle\Wireless;

use Psr\Log\LoggerInterface;
use AppBundle\Command\Executor;
use AppBundle\Wireless\Network;

class Scanner
{
    /**
    * @var LoggerInterface
    */
    private $logger;

    /**
    * @var Executor
    */
    private $commandExecutor;

    public function __construct(LoggerInterface $logger, Executor $commandExecutor)
    {
        $this->logger = $logger;
        $this->commandExecutor = $commandExecutor;
    }

    /**
     * Scans wireless networks.
     *
     * @return void
     */
    public function scan()
    {
        $command = $this->commandExecutor->execute('wpa_cli scan');

        if ($command->isValid()) {
            $output = implode(' ', $command->getOutput());
            preg_match('/Selected interface \'([a-z0-9]+)\'/i', $output, $result);

            $selectedInterface = isset($result[0]) ? $result[0] : 'Unknown';

            $this->logger->info('Scanning networks... Selected interface: '.$selectedInterface);
        }
    }

    /**
     * Scans wireless networks and returns an array with the scan results.
     *
     * @return array The scan results.
     */
    public function getNetworks()
    {
        $this->scan();
        $command = $this->commandExecutor->execute('wpa_cli scan_results');
        $networks = array();

        if ($command->isValid()) {
            $output = $command->getOutput();

            // Remove comment rows
            for ($i = 0; $i < 2; $i++) {
                array_shift($output);
            }

            foreach ($output as $row) {
                $networkRow = preg_split('/[\t]+/', $row);
                $network = new Network($networkRow[0], $networkRow[1], $networkRow[2], $networkRow[3], $networkRow[4]);

                $networks[] = $network->getDetails();
            }

            $this->logger->info('Found '.$command->getOutputCount().' networks.');
        }

        return $networks;
    }
}
