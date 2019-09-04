<?php

namespace PbLvApi;

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

    const OPERATION_CREATE_ORDER = 'CreateOrder';
    const OPERATION_COMPLETION = 'Completion';
    const OPERATION_REVERSE = 'Reverse';
    const OPERATION_REFUND = 'Refund';
    const OPERATION_GET_ORDER_STATUS = 'GetOrderStatus';
    const OPERATION_GET_ORDER_INFORMATION = 'GetOrderInformation';
    const OPERATION_GET_ORDERS = 'GetOrders';
    const OPERATION_GET_CHECK_INFO = 'GetCheckInfo';
    const OPERATION_GET_CHECK_LIST = 'GetCheckList';

    private $operations = [
        self::OPERATION_CREATE_ORDER,
        self::OPERATION_COMPLETION,
        self::OPERATION_REVERSE,
        self::OPERATION_REFUND,
        self::OPERATION_GET_ORDER_STATUS,
        self::OPERATION_GET_ORDER_INFORMATION,
        self::OPERATION_GET_ORDERS,
        self::OPERATION_GET_CHECK_INFO,
        self::OPERATION_GET_CHECK_LIST
    ];

    const LANGUAGE_EN = 'EN';
    const LANGUAGE_UA = 'UA';
    const LANGUAGE_RU = 'RU';
    const LANGUAGE_LV = 'LV';

    private $languages = [
        self::LANGUAGE_EN,
        self::LANGUAGE_UA,
        self::LANGUAGE_RU,
        self::LANGUAGE_LV,
    ];

    private $apiAuthUrl = "https://twecp.privatbank.lv:8443/Exec";

    private $keyPath;

    private $certPath;

    private $certPass;

    private $merchant;

    private $language;

    /**
     * Constructor
     *
     * @param array $params
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($params)
    {
        if(empty($params['certPath']))
            throw new \InvalidArgumentException("certPath parameter is required!");

        if(empty($params['certPass']))
            throw new \InvalidArgumentException("certPass parameter is required!");

        if(empty($params['keyPath']))
            throw new \InvalidArgumentException("keyPath parameter is required!");

        if(empty($params['merchant']))
            throw new \InvalidArgumentException("merchant parameter is required!");

        if(empty($params['language'] || in_array($params['language'], $this->languages)))
            throw new \InvalidArgumentException(
                "language parameter is required or wrong language code!Supported language codes: "
                . implode(', ', $this->languages)
            );

        $this->keyPath = $params['keyPath'];

        $this->certPath = $params['certPath'];

        $this->certPass = $params['certPass'];

        $this->merchant = $params['merchant'];

        $this->language = $params['language'];
    }

    /**
     * Create order
     *
     * @param array $params
     *
     * @return string
     */
    public function createOrder($params){

        if(empty($params['amount']))
            throw new \InvalidArgumentException("amount parameter is required!");

        if(empty($params['language']))
            throw new \InvalidArgumentException("language parameter is required!");

        if(empty($params['currency']) || ! in_array($params['currency'], $this->currencies))
            throw new \InvalidArgumentException("currency parameter is required or wrong currency code!");

        $tkkpg = new \SimpleXMLElement("<TKKPG></TKKPG>");
        $tkkpg->addChild('Request');

        $request = $tkkpg->Request;

        $request->addChild('Operation', 'CreateOrder');
        $request->addChild('Language', $this->language);
        $request->addChild('Order');

        $order = $request->Order;
        $order->addChild('Merchant', $this->merchant);
        $order->addChild('Amount', floor($params['amount'] * 100));
        $order->addChild('Currency', $params['currency']);

        if( ! empty($params['orderType']))
            $order->addChild('OrderType', $params['orderType']);

        if( ! empty($params['description']))
            $order->addChild('Description', $params['description']);

        if( ! empty($params['approveUrl']))
            $order->addChild('ApproveUrl', $params['approveUrl']);

        if( ! empty($params['cancelUrl']))
            $order->addChild('CancelUrl', $params['cancelUrl']);

        if( ! empty($params['declineUrl']))
            $order->addChild('DeclineUrl', $params['declineUrl']);

        if( ! empty($params['phone']))
            $order->addChild('Phone', $params['phone']);

        if( ! empty($params['addParams']) && is_array($params['addParams'])){
            $order->addChild('AddParams');

            $addParams = $order->AddParams;

            foreach($params['addParams'] as $key => $param){
                $addParams->addChild(ucfirst($key), $param);
            }
        }

        $response = $this->request($tkkpg->asXML());

        return $response;
    }


    /**
     * Get orders list
     *
     * @param array $params
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getOrders(array $params){

        if( ! isset($params['filters']['period']['start']) && ! isset($params['filters']['lastCount']))
            throw new \InvalidArgumentException("filters/period/start or filters/lastCount parameter is required!");

        $tkkpg = new \SimpleXMLElement("<TKKPG></TKKPG>");

        $request = $tkkpg->addChild('Request');
        $request->addChild('Operation', 'GetOrders');
        $request->addChild('Merchant', $this->merchant);
        $request->addChild('Language', $this->language);

        if( ! empty($params['filters'])){
            $filters = $request->addChild('OrdersFilter');

            if(isset($params['filters']['lastCount']))
                $filters->addChild('LastCount', $params['filters']['lastCount']);

            if(isset($params['filters']['startFrom']))
                $filters->addChild('StartFrom', $params['filters']['startfrom']);

            if(isset($params['filters']['status']))
                $filters->addChild('Status', $params['filters']['status']);

            if(isset($params['filters']['period'])){
                $period = $filters->addChild('Period');

                if(isset($params['filters']['period']['start']))
                    $period->addChild('Start', $params['filters']['period']['start']);

                if(isset($params['period']['end']))
                    $period->addChild('End', $params['filters']['period']['end']);
            }

            if(isset($params['filters']['amount'])){
                $amount = $filters->addChild('Amount');

                if(isset($params['filters']['amount']['min']))
                    $amount->addChild('Min', $params['filters']['amount']['min']);

                if(isset($params['filters']['amount']['max']))
                    $amount->addChild('Max', $params['filters']['amount']['max']);
            }

            if(isset($params['filters']['description']))
                $filters->addChild('Description', $params['filters']['description']);

            if(isset($params['filters']['orderBy']))
                $filters->addChild('OrderBy', $params['filters']['orderBy']);

            if(isset($params['filters']['orderingDirection']))
                $filters->addChild('OrderingDirection', $params['filters']['orderingDirection']);
        }

        $respose = $this->request($tkkpg->asXML());

        return $respose;
    }



    /**
     * Generate payment link
     *
     * @param array $params
     *
     * @return string
     */
    public function getOrderStatus($params){
        $tkkpg = new \SimpleXMLElement('<TKKPG></TKKPG>');

        if( ! isset($params['sessionId']))
            throw new \InvalidArgumentException('sessionId parameter is required');

        if( ! isset($params['orderId']))
            throw new \InvalidArgumentException('orderId parameter is required');

        $request = $tkkpg->addChild('Request');
        $request->addChild('Operation', 'GetOrderStatus');
        $request->addChild('Language', $this->language);
        $request->addChild('SessionID', $params['sessionId']);

        $order = $request->addChild('Order');
        $order->addChild('Merchant', $this->merchant);
        $order->addChild('OrderID', $params['orderId']);

        $response = $this->request($tkkpg->asXML());

        return $response;
    }

    /**
     * Send request
     *
     * @param string $xmlData
     *
     * @param string $type
     *
     * @throws \ErrorException
     *
     * @return Object
     */
    protected function request($xmlData){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        curl_setopt($ch, CURLOPT_URL, $this->apiAuthUrl);
        curl_setopt($ch, CURLOPT_SSH_PRIVATE_KEYFILE, $this->certPath);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->certPass);
        curl_setopt($ch, CURLOPT_CAINFO, $this->keyPath);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->keyPath);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE , 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $ch_result = curl_exec($ch);

        if(curl_errno($ch))
            throw new \ErrorException(curl_error($ch));

        curl_close($ch);
        return simplexml_load_string($ch_result);
    }

}