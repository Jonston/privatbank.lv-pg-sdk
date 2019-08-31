<?php

namespace PbLvApi;

use Psr\Log\InvalidArgumentException;

class PbLvApi{

    const CURRENCY_JPY = 392;
    const CURRENCY_KZT = 398;
    const CURRENCY_LVL = 428;
    const CURRENCY_RUR = 810;
    const CURRENCY_GBP = 826;
    const CURRENCY_USD = 840;
    const CURRENCY_AZN = 944;
    const CURRENCY_BYR = 974;
    const CURRENCY_BGN = 975;
    const CURRENCY_EUR = 978;
    const CURRENCY_UAH = 980;
    const CURRENCY_GEL = 981;
    const CURRENCY_PLN = 985;

    private $currencies = [
        self::CURRENCY_JPY,
        self::CURRENCY_KZT,
        self::CURRENCY_LVL,
        self::CURRENCY_RUR,
        self::CURRENCY_GBP,
        self::CURRENCY_USD,
        self::CURRENCY_AZN,
        self::CURRENCY_BYR,
        self::CURRENCY_BGN,
        self::CURRENCY_EUR,
        self::CURRENCY_UAH,
        self::CURRENCY_GEL,
        self::CURRENCY_PLN
    ];

    private $apiAuthUrl = "https://twecp.privatbank.lv:8443/Exec";

    private $keyPath;

    private $certPath;

    private $certPass;

    private $merchant;

    /**
     * Constructor
     *
     * @param array $params
    */
    public function __construct($params)
    {
        if(empty($params['certPath']))
            throw new InvalidArgumentException("certPath parameter is required!");

        if(empty($params['certPass']))
            throw new InvalidArgumentException("certPass parameter is required!");

        if(empty($params['keyPath']))
            throw new InvalidArgumentException("keyPath parameter is required!");

        if(empty($params['merchant']))
            throw new InvalidArgumentException("merchant parameter is required!");

        $this->keyPath = $params['keyPath'];

        $this->certPath = $params['certPath'];

        $this->certPass = $params['certPass'];

        $this->merchant = $params['merchant'];
    }

}