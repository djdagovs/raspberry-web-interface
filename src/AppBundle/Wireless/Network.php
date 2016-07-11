<?php

namespace AppBundle\Wireless;

class Network
{
    const STATUS_CURRENT = 'Current';
    const STATUS_ENABLED = 'Enabled';
    const STATUS_DISABLED = 'Disabled';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $ssid;

    /**
     * @var string
     */
    private $bssid;

    /**
     * @var string
     */
    private $flags;

    /**
     * @var string
     */
    private $status;

    public function __construct($id, $ssid, $bssid, $flags)
    {
        $this->id = $id;
        $this->ssid = $ssid;
        $this->bssid = $bssid;
        $this->flags = $flags;

        // Parse flags
        $this->parseFlags();
    }

    /**
     * Gets the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the value of ssid.
     *
     * @return string
     */
    public function getSsid()
    {
        return $this->ssid;
    }

    /**
     * Gets the value of bssid.
     *
     * @return string
     */
    public function getBssid()
    {
        return $this->bssid;
    }

    /**
     * Gets the value of flags.
     *
     * @return string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Gets the value of status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns true if the network is the current network.
     *
     * @return boole True if the network is the current network.
     */
    public function isCurrent()
    {
        return $this->status == Network::STATUS_CURRENT;
    }

    /**
     * Returns an array of network details.
     *
     * @return array The network details.
     */
    public function getDetails()
    {
        return [
            'id' => $this->getId(),
            'ssid' => $this->getSsid(),
            'bssid' => $this->getBssid(),
            'status' => $this->getStatus(),
            'current' => $this->isCurrent(),
        ];
    }

/**
     * Checks if the network has the given flag.
     *
     * @param $flag string The flag to check.
     * @return bool True if the network has the given flag, false otherwise.
     */
    private function hasFlag($flag)
    {
        preg_match('/(?P<current>\[CURRENT\])?(?P<enabled>\[ENABLED\])?(?P<disabled>\[DISABLED\])?/i', $this->flags, $matches);

        return array_key_exists($flag, $matches) && !empty($matches[$flag]);
    }

    /**
     * Checks if the network has the given array of flags.
     *
     * @param $flags array The array of flags to check.
     * @return bool True if the network has the given flags, false otherwise.
     */
    private function hasFlags(array $flags)
    {
        foreach ($flags as $flag) {
            if (!$this->hasFlag($flag)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parses the network flags.
     *
     * @return void
     */
    public function parseFlags()
    {
        // Status
        if ($this->hasFlag('current')) {
            $this->status = Network::STATUS_CURRENT;
        }

        if ($this->hasFlag('enabled')) {
            $this->status = Network::STATUS_ENABLED;
        }

        if ($this->hasFlag('disabled')) {
            $this->status = Network::STATUS_DISABLED;
        }
    }

    /**
     * Make a network instance from the wpa_cli list_network results.
     *
     * @param string $row A network row from the cpa_cli list_network command.
     * @return Network
     */
    public static function fromRow($row)
    {
        if (preg_match('/(?P<id>[0-9]+)\s?(?P<ssid>[a-zA-Z0-9 ]+)?\s?(?P<bssid>[0-9a-zA-Z\:]+)?\s?(?P<flags>.*)?/', $row, $matches)) {
            $id = $matches['id'];
            $ssid = isset($matches['ssid']) ? $matches['ssid'] : null;
            $bssid = $matches['bssid'];
            $flags = $matches['flags'];

            $network = new Network($id, $ssid, $bssid, $flags);

            return $network;
        }

        return null;
    }
}
