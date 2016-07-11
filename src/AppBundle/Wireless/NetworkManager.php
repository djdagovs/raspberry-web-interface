<?php

namespace AppBundle\Wireless;

use Psr\Log\LoggerInterface;
use AppBundle\Command\Executor;
use AppBundle\Network\NetworkInterface;
use AppBundle\Exception\FileNotReadableException;

class NetworkManager
{
    /**
     * @var NetworkInterface
     */
    private $interface;

    /**
     * @var string
     */
    private $configurationFile;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandExecutor
     */
    private $commandExecutor;

    public function __construct(NetworkInterface $interface, $configurationFile, LoggerInterface $logger, Executor $commandExecutor)
    {
        $this->interface = $interface;
        $this->setConfigurationFile($configurationFile);
        $this->logger = $logger;
        $this->commandExecutor = $commandExecutor;
    }

    /**
     * Sets the configuration file used for the wpa_cli tool.
     *
     * @param string $configurationFile The path of the configuration file that is used by the wpa_cli tool.
     */
    public function setConfigurationFile($configurationFile)
    {
        if (!file_exists($configurationFile)) {
            throw new FileNotReadableException('The configuration file "'.$configurationFile.'" does not exist, or isn\'t readable.');
        }

        $this->configurationFile = $configurationFile;
    }

    /**
     * Returns an array of saved networks.
     *
     * @return array An array of saved networks.
     */
    public function listNetworks()
    {
        $command = $this->commandExecutor->execute('wpa_cli -i '.$this->interface->getName().' list_networks');
        $networks = array();

        if ($command->isValid()) {
            $output = $command->getOutput();

            // Remove comment rows
            for ($i = 0; $i < 1; $i++) {
                array_shift($output);
            }

            foreach ($output as $row) {
                $result = Network::fromRow($row);
                $networks[] = $result->getDetails();
            }

            $this->logger->info('Found '.$command->getOutputCount().' saved networks.');
        }

        return $networks;
    }
}
