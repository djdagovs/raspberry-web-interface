<?php

namespace AppBundle\Network;

use AppBundle\Command\Executor;
use AppBundle\Network\ConfigurationReader;

class NetworkInterfaceWirelessConnection
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
    private $networkSsid;

    /**
     * @var string
     */
    private $networkBssid;

    /**
     * @var string
     */
    private $networkBitrate;

    /**
     * @var string
     */
    private $networkTxPower;

    /**
     * @var string
     */
    private $networkLinkQuality;

    /**
     * @var string
     */
    private $networkSignalLevel;

    /**
     * @var string
     */
    private $networkFrequency;

    public function __construct($interfaceName, Executor $commandExecutor)
    {
        $this->commandExecutor = $commandExecutor;
        $this->name = $interfaceName;
    }

    /**
     * Gets the value of networkSsid.
     *
     * @return string
     */
    public function getNetworkSsid()
    {
        if (is_null($this->networkSsid)) {
            $this->populateNetworkConnectionDetails();
        }

        return $this->networkSsid;
    }

    /**
     * Gets the value of networkBssid.
     *
     * @return string
     */
    public function getNetworkBssid()
    {
        if (is_null($this->networkBssid)) {
            $this->populateNetworkConnectionDetails();
        }

        return $this->networkBssid;
    }

    /**
     * Gets the value of networkBitrate.
     *
     * @return string
     */
    public function getNetworkBitrate()
    {
        if (is_null($this->networkBitrate)) {
            $this->populateNetworkConnectionDetails();
        }

        return $this->networkBitrate;
    }

    /**
     * Gets the value of networkTxPower.
     *
     * @return string
     */
    public function getNetworkTxPower()
    {
    	if (is_null($this->networkTxPower)) {
            $this->populateNetworkConnectionDetails();
        }

        return $this->networkTxPower;
    }

    /**
     * Gets the value of networkLinkQuality.
     *
     * @return string
     */
    public function getNetworkLinkQuality()
    {
    	if (is_null($this->networkLinkQuality)) {
            $this->populateNetworkConnectionDetails();
        }

        return $this->networkLinkQuality;
    }

    /**
     * Returns the network link quality as a percentage.
     *
     * @return string
     */
    public function getNetworkLinkQualityPercentage()
    {
        $networkLinkQuality = $this->getNetworkLinkQuality();

        if (!is_null($networkLinkQuality)) {
            return ($networkLinkQuality[1] / $networkLinkQuality[2]) * 100;
        }

        return null;
    }

    /**
     * Gets the value of networkSignalLevel.
     *
     * @return string
     */
    public function getNetworkSignalLevel()
    {
        if (is_null($this->networkSignalLevel)) {
            $this->populateNetworkConnectionDetails();
        }

        return $this->networkSignalLevel;
    }

    /**
     * Gets the value of networkSignalLevel as a percentage.
     *
     * @see http://stackoverflow.com/a/15798024
     * @return string
     */
    public function getNetworkSignalLevelPercentage()
    {
        $networkSignalLevel = $this->getNetworkSignalLevel();

        if (!is_null($networkSignalLevel)) {
            if ($networkSignalLevel < -100) {
                return 0;
            } elseif ($networkSignalLevel > -50) {
                return 100;
            } else {
                return 2 * ($networkSignalLevel + 100);
            }
        }

        return null;
    }

    /**
     * Gets the value of networkFrequency.
     *
     * @return string
     */
    public function getNetworkFrequency()
    {
        if (is_null($this->networkFrequency)) {
            $this->populateNetworkConnectionDetails();
        }

        return $this->networkFrequency;
    }

    /**
     * Populates the network connection details.
     *
     * @return bool True if the population succeeded, false if not.
     */
    private function populateNetworkConnectionDetails()
    {
        $command = $this->commandExecutor->execute('/sbin/iwconfig '.$this->name);

        if ($command->isValid()) {
            $iwconfig = implode(' ', $command->getOutput());
            $iwconfig = preg_replace('/\s\s+/', ' ', $iwconfig);

            $configurationReader = new ConfigurationReader($iwconfig);

            $this->networkSsid = $configurationReader->read('/ESSID:\"([a-zA-Z0-9\s]+)\"/i');
            $this->networkBssid = $configurationReader->read('/Access Point: ([0-9a-f:]+)/i');
            $this->networkBitrate = $configurationReader->read('/Bit Rate=([0-9.]+ Mb\/s)/i');
            $this->networkTxPower = $configurationReader->read('/Tx-Power=([0-9]+ dBm)/i');
            $this->networkLinkQuality = $configurationReader->read('/Link Quality=([0-9]+)\/([0-9]+)/i');
            $this->networkSignalLevel = $configurationReader->read('/Signal level=([\-0-9]+)/i');
            $this->networkFrequency = $configurationReader->read('/Frequency:(\d+.\d+ GHz)/i');

            return true;
        }

        return false;
    }
}
