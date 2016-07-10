<?php

namespace AppBundle\Network;

use Psr\Log\LoggerInterface;
use AppBundle\Command\Executor;
use AppBundle\Network\ConfigurationReader;
use AppBundle\Network\NetworkInterfaceWirelessConnection;

class NetworkInterface
{
    /**
     * @var Executor
     */
    private $commandExecutor;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $operationState;

    /**
     * @var string
     */
    private $macAddress;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var string
     */
    private $netmask;

    /**
     * @var string
     */
    private $rxPacketsCount;

    /**
     * @var string
     */
    private $txPacketsCount;

    /**
     * @var string
     */
    private $rxBytesCount;

    /**
     * @var string
     */
    private $txBytesCount;

    /**
     * @var NetworkInterfaceWirelessConnection
     */
    private $wirelessConnection;

    public function __construct($interfaceName, Executor $commandExecutor)
    {
        $this->commandExecutor = $commandExecutor;
        $this->name = $interfaceName;
    }

    /**
     * Gets the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the operation state of the interface.
     *
     * @see https://www.kernel.org/doc/Documentation/networking/operstates.txt
     * @return array The operation state of the interface with the label class.
     */
    public function getOperationState()
    {
        $operationStates = [
            'up' => ['Running' ,'success'],
            'dormant' => ['Waiting...', 'warning'],
            'notPresent' => ['Not present', 'warning'],
            'down' => ['Interface is down', 'danger'],
        ];

        if (!is_null($this->operationState)) {
            if ($this->populateInterfaceConfiguration()) {
                return $this->operationState;
            }
        }

        return null;
    }

    /**
     * Returns the MAC address of the interface.
     *
     * @return string The MAC address of the interface.
     */
    public function getMacAddress()
    {
        if (!is_null($this->macAddress)) {
            if ($this->populateInterfaceConfiguration()) {
                return $this->macAddress;
            }
        }

        return null;
    }

    /**
     * Returns the IP address of the interface.
     *
     * @return string The IP address of the interface.
     */
    public function getIpAddress()
    {
        if (!is_null($this->ipAddress)) {
            if ($this->populateInterfaceConfiguration()) {
                return $this->ipAddress;
            }
        }

        return null;
    }

    /**
     * Returns the netmask of the interface.
     *
     * @return string The netmask of the interface.
     */
    public function getNetmask()
    {
        if (!is_null($this->netmask)) {
            if ($this->populateInterfaceConfiguration()) {
                return $this->netmask;
            }
        }

        return null;
    }

    /**
     * Returns the RX packets count of the interface.
     *
     * @return string The RX packets count of the interface.
     */
    public function getRxPacketsCount()
    {
        if (!is_null($this->rxPacketsCount)) {
            if ($this->populateInterfaceConfiguration()) {
                return $this->rxPacketsCount;
            }
        }

        return null;
    }

    /**
     * Returns the TX packets count of the interface.
     *
     * @return string The TX packets count of the interface.
     */
    public function getTxPacketsCount()
    {
        if (!is_null($this->txPacketsCount)) {
            if ($this->populateInterfaceConfiguration()) {
                return $this->txPacketsCount;
            }
        }

        return null;
    }

    /**
     * Returns the RX bytes count of the interface.
     *
     * @return string The RX bytes count of the interface.
     */
    public function getRxBytesCount()
    {
        if (!is_null($this->rxBytesCount)) {
            if ($this->populateInterfaceConfiguration()) {
                return $this->rxBytesCount;
            }
        }

        return null;
    }

    /**
     * Returns the TX bytes count of the interface.
     *
     * @return string The TX bytes count of the interface.
     */
    public function getTxBytesCount()
    {
        if (!is_null($this->txBytesCount)) {
            if ($this->populateInterfaceConfiguration()) {
                return $this->txBytesCount;
            }
        }

        return null;
    }

    /**
     * Returns the connection of the interface.
     *
     * @return string The connection of the interface.
     */
    public function getWirelessConnection()
    {
        $command = $this->commandExecutor->execute('/sbin/iwconfig '.$this->name);

        if ($command->isValid()) {
            $output = implode(' ', $command->getOutput());

            if (empty($output) || strpos('no wireless extensions', $output) !== false) {
                return null;
            }
        }

        if (is_null($this->wirelessConnection)) {
            $this->wirelessConnection = new NetworkInterfaceWirelessConnection($this->name, $this->commandExecutor);
        }

        return $this->wirelessConnection;
    }

    /**
     * Returns an array of wireless connection details.
     *
     * @return array The wireless connection details of the interface.
     */
    public function getWirelessConnectionDetails()
    {
        $wirelessConnection = $this->getWirelessConnection();

        if (!is_null($wirelessConnection)) {
            return [
                'ssid' => $wirelessConnection->getNetworkSsid(),
                'bssid' => $wirelessConnection->getNetworkBssid(),
                'bitrate' => $wirelessConnection->getNetworkBitrate(),
                'frequency' => $wirelessConnection->getNetworkFrequency(),
                'link_quality' => $wirelessConnection->getNetworkLinkQualityPercentage(),
                'signal_level' => $wirelessConnection->getNetworkSignalLevel(),
            ];
        }

        return null;
    }

    /**
     * Populates the interface configuration details.
     *
     * @return bool True if the population succeeded, false if not.
     */
    private function populateInterfaceConfiguration()
    {
        $command = $this->commandExecutor->execute('/sbin/ifconfig '.$this->name);

        if ($command->isValid()) {
            $ifconfig = implode(' ', $command->getOutput());
            $ifconfig = preg_replace('/\s\s+/', ' ', $ifconfig);

            $configurationReader = new ConfigurationReader($ifconfig);

            $this->macAddress = $configurationReader->read('/HWaddr ([0-9a-f:]+)/i');
            $this->ipAddress = $configurationReader->read('/inet addr:([0-9.]+)/i');
            $this->netmask = $configurationReader->read('/Mask:([0-9.]+)/i');
            $this->rxPacketsCount = $configurationReader->read('/RX packets:(\d+)/');
            $this->txPacketsCount = $configurationReader->read('/TX packets:(\d+)/');
            $this->rxBytesCount = $configurationReader->read('/RX bytes:(\d+) \((\d+.\d+ [K|M|G]iB)\)/i');
            $this->txBytesCount = $configurationReader->read('/TX Bytes:(\d+) \((\d+.\d+ [K|M|G]iB)\)/i');

            return true;
        }

        return false;
    }

    /**
     * Returns the interface with the given name.
     *
     * @param string $interfaceName The interface name, example: eth0 or wlan0.
     * @param Executor $commandExecutor The command executor service.
     * @return NetworkInterface The network interface object.
     */
    public static function get($interfaceName, Executor $commandExecutor)
    {
        return new NetworkInterface($interfaceName, $commandExecutor);
    }

    /**
     * Returns all network interfaces.
     *
     * @param Executor $commandExecutor The command executor service.
     * @return array|bool An array with NetworkInterface objects.
     */
    public static function getAll(Executor $commandExecutor)
    {
        $command = $commandExecutor->execute('ls /sys/class/net -1');

        if ($command->isValid()) {
            $interfaces = array();

            foreach ($command->getOutput() as $interfaceName) {
                $interfaces[] = NetworkInterface::get($interfaceName, $commandExecutor);
            }

            return $interfaces;
        }

        return false;
    }
}
