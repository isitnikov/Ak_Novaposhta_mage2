<?php
namespace Ak\NovaPoshta\Model\Api;

class Client extends \Magento\Framework\Object
{
    /** @var \Ak\NovaPoshta\Helper\Data */
    protected $_helper;

    /** @var \Magento\Framework\HTTP\ZendClientFactory */
    protected $_httpClientFactory;

    /** @var \Magento\Framework\HTTP\ZendClient|null */
    protected $_httpClient;

    const DELIVERY_TYPE_APARTMENT_APARTMENT = 1;
    const DELIVERY_TYPE_APARTMENT_WAREHOUSE = 2;
    const DELIVERY_TYPE_WAREHOUSE_APARTMENT = 3;
    const DELIVERY_TYPE_WAREHOUSE_WAREHOUSE = 4;

    const LOAD_TYPE_STANDARD   = 1;
    const LOAD_TYPE_SECURITIES = 4;

    public function __construct(
        \Ak\NovaPoshta\Helper\Data $helper,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_httpClientFactory = $httpClientFactory;
        $this->_helper = $helper;
    }

    /**
     * @return string
     */
    protected function _getApiUri()
    {
        return $this->_helper->getStoreConfig('api_url');
    }

    /**
     * @return string
     */
    protected function _getApiKey()
    {
        return $this->_helper->getStoreConfig('api_key');
    }

    /**
     * @return \Magento\Framework\HTTP\ZendClient
     */
    protected function _getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = $this->_httpClientFactory->create();
            $this->_httpClient->setUri($this->_getApiUri());
        }

        return $this->_httpClient;
    }

    /**
     * @param array $array
     * @param \SimpleXMLElement $element
     * @return \SimpleXMLElement
     */
    protected function _buildXml(array $array, \SimpleXMLElement $element = null)
    {
        if (is_null($element)) {
            $element = new \SimpleXMLElement('<file/>');
            $element->addChild('auth', $this->_getApiKey());
        }

        foreach ($array as $key => $value) {
            if (!is_numeric($key)) {
                if (is_array($value)) {
                    $this->_buildXml($value, $element->addChild($key));
                } else {
                    $element->addChild($key, $value);
                }
            }
        }

        return $element;
    }

    /**
     * @param array $data
     * @return \SimpleXMLElement
     */
    protected function _makeRequest(array $data)
    {
        /** @var \Ak\NovaPoshta\Helper\Data $helper */
        $helper    = $this->_helper;
        $xmlString = $this->_buildXml($data)->asXML();

        $helper->log(__('Request XML:') . $xmlString);

        /** @var \Zend_Http_Response $response */
        $response = $this->_getHttpClient()
            ->resetParameters(true)
            ->setRawData($xmlString)
            ->request(\Zend_Http_Client::POST);

        $helper->log(__('Response status code:') . $response->getStatus());
        $helper->log(__('Response body:') . $response->getBody());
        $helper->log(print_r((array) new \SimpleXMLElement($response->getBody()), true));

        if (200 != $response->getStatus()) {
            throw new \Magento\Framework\Model\Exception(__('Server error, response status:') . $response->getStatus());
        }

        return new \SimpleXMLElement($response->getBody());
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getCityWarehouses()
    {
        $responseXml = $this->_makeRequest(array(
            'citywarehouses' => null,
        ));

        return $responseXml->xpath('result/cities/city');
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getWarehouses()
    {
        $responseXml = $this->_makeRequest(array(
            'warenhouse' => null,
        ));

        return $responseXml->xpath('result/whs/warenhouse');
    }

    /**
     * @param \Magento\Framework\Stdlib\DateTime\Date $deliveryDate
     * @param \Ak\NovaPoshta\Model\City $senderCity
     * @param \Ak\NovaPoshta\Model\City $recipientCity
     * @param int|float $packageWeight
     * @param int|float $packageLength
     * @param int|float $packageWidth
     * @param int|float $packageHeight
     * @param int|float $publicPrice
     * @param int|float $deliveryType
     * @param int|float $loadType
     * @param int|float $floor
     *
     * @return array
     *
     * @throws \Magento\Framework\Model\Exception
     */
    public function getShippingCost(
        \Magento\Framework\Stdlib\DateTime\Date $deliveryDate,
        \Ak\NovaPoshta\Model\City $senderCity,
        \Ak\NovaPoshta\Model\City $recipientCity,
        $packageWeight,
        $packageLength,
        $packageWidth,
        $packageHeight,
        $publicPrice,
        $deliveryType = self::DELIVERY_TYPE_WAREHOUSE_WAREHOUSE,
        $loadType = self::LOAD_TYPE_STANDARD,
        $floor = 0
    ) {
        $response = $this->_makeRequest(array(
            'countPrice' => array(
                'date'            => $deliveryDate->toString(\Magento\Framework\Stdlib\DateTime\Date::DATE_MEDIUM),
                'senderCity'      => $senderCity->getData('name_ru'),
                'recipientCity'   => $recipientCity->getData('name_ru'),
                'mass'            => $packageWeight,
                'depth'           => $packageLength,
                'widht'           => $packageWidth,
                'height'          => $packageHeight,
                'publicPrice'     => $publicPrice,
                'deliveryType_id' => $deliveryType,
                'loadType_id'     => $loadType,
                'floor_count'     => $floor,
            )
        ));

        if (1 == (int) $response->error) {
            throw new \Magento\Framework\Model\Exception(__('Novaposhta Api error'));
        }

        return array (
            'delivery_date' => (string) $response->date,
            'cost'          => (float) $response->cost,
        );
    }
}
