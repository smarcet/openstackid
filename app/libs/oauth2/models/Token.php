<?php

namespace oauth2\models;

use DateTime;
use DateInterval;
use DateTimeZone;
use utils\IPHelper;

/**
 * Class Token
 * Defines the common behavior for all emitted tokens
 * @package oauth2\models
 */
abstract class Token
{

    const DefaultByteLength = 32;

    protected $value;
    protected $lifetime;
    protected $issued;
    protected $client_id;
    protected $len;
    protected $scope;
    protected $audience;
    protected $from_ip;
    protected $is_hashed;
    protected $user_id;

    public function __construct($len = self::DefaultByteLength)
    {
        $this->len       = $len;
        $this->is_hashed = false;
        $this->issued    = gmdate("Y-m-d H:i:s", time());
	    $this->from_ip   = IPHelper::getUserIp();
    }

    public function getIssued()
    {
        return $this->issued;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLifetime()
    {
        return intval($this->lifetime);
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getAudience()
    {
        return $this->audience;
    }

    public function getFromIp()
    {
        return $this->from_ip;
    }

    public function getUserId(){
        return $this->user_id;
    }

    public function getRemainingLifetime()
    {
        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if (intval($this->lifetime) == 0) return 0;
        $created_at = new DateTime($this->issued, new DateTimeZone("UTC"));
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now = new DateTime(gmdate("Y-m-d H:i:s", time()), new DateTimeZone("UTC"));
        //check validity...
        if ($now > $created_at)
            return -1;
        $seconds = abs($created_at->getTimestamp() - $now->getTimestamp());;
        return $seconds;
    }

    public function isHashed(){
        return $this->is_hashed;
    }

    public abstract function toJSON();


    public abstract function fromJSON($json);
} 