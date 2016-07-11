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
            // Offset 1 removes the comment lines
            $output = $command->getOutput(1);

            foreach ($output as $row) {
                $result = Network::fromRow($row);

                if (!is_null($result)) {
                    $networks[] = $result->getDetails();
                }
            }

            $this->logger->info('Found '.$command->getOutputCount(1).' saved network(s).');
        }

        return $networks;
    }

    /**
     * Adds the given network to the configuration file.
     *
     * @param string $ssid The SSID of the network to add.
     * @param string $password The password of the network to add, null for open networks or other networks.
     * @param string $preferredBssid The preferred BSSID to connect with, null for any.
     * @param string $keyManagement The key management type to use, default WPA-PSK.
     */
    public function addNetwork($ssid, $password = null, $preferredBssid = null, $keyManagement = 'WPA-PSK')
    {
        // Set default key management if there is no key management method set.
        if (!is_null($password) && (is_null($keyManagement) || empty($keyManagement))) {
            $keyManagement = 'WPA-PSK';
        }

        // Create wpa_supplicant daemon and add network
        $command = $this->commandExecutor->execute(sprintf('wpa_cli -i %s add_network', $this->interface->getName()));

        if ($command->isValid()) {
            $output = $command->getOutput();

            if (isset($output[0])) {
                $networkId = $output[0];
                $this->commandExecutor->execute(sprintf('wpa_cli set_network %d ssid \'"%s"\'', $networkId, $ssid));

                if (is_null($password)) {
                    // Add network without PSK, set key management to none.
                    $this->commandExecutor->execute(sprintf('wpa_cli set_network %d key_mgmt NONE', $networkId));
                } else {
                    // Add network with PSK and set key management.
                    $this->commandExecutor->execute(sprintf('wpa_cli set_network %d key_mgmt %s', $networkId, $keyManagement));
                }

                // Set preferred BSSID if not null
                if (!is_null($preferredBssid)) {
                    $command = $this->commandExecutor->execute(sprintf('wpa_cli bssid %d %s', $networkId, $preferredBssid));
                }

                $command = $this->commandExecutor->execute(sprintf('wpa_cli enable_network %d', $networkId));
            }
        }

        // Check if the network is added
        if ($command->isValid()) {
            $this->logger->info(sprintf('Succesfully added "%s" to the WPA supplicant configuration file.', $ssid), $command->getOutput());

            return true;
        } else {
            $this->logger->error(sprintf('Could not add "%s" to the WPA supplicant configuration file.', $ssid), $command->getOutput());

            return false;
        }
    }

    /**
     * Enables the network with the given ID.
     *
     * @param int $id The ID of the network to enable.
     * @return bool True if the network was enabled, false if not.
     */
    public function enableNetwork($id)
    {
        $this->interface->down();
        $command = $this->commandExecutor->execute(sprintf('wpa_cli enable_network %d', $id));
        $this->interface->up();

        return $command->isValid();
    }

    /**
     * Disables the network with the given ID.
     *
     * @param int $id The ID of the network to disable.
     * @return bool True if the network was disabled, false if not.
     */
    public function disableNetwork($id)
    {
        $this->interface->down();
        $command = $this->commandExecutor->execute(sprintf('wpa_cli disable_network %d', $id));
        $this->interface->up();

        return $command->isValid();
    }

    /**
     * Removes the network with the given ID.
     *
     * @param int $id The ID of the network to remove.
     * @return bool True if the network was removed, false if not.
     */
    public function removeNetwork($id)
    {
        $this->interface->down();
        $command = $this->commandExecutor->execute(sprintf('wpa_cli remove_network %d', $id));
        $this->interface->up();

        return $command->isValid();
    }
}
