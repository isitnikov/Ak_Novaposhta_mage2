<?php
namespace Ak\Novaposhta\Helper;

use Magento\Framework\Stdlib\DateTime;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_logFile = 'novaposhta.log';

    /**
     * Log adapter factory
     *
     * @var \Magento\Framework\Logger\AdapterFactory
     */
    protected $_logFactory;

    /** @var \Ak\NovaPoshta\Model\Resource\City */
    protected $_senderCity;

    /** @var \Ak\NovaPoshta\Model\CityFactory */
    protected $_cityFactory;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    /** @var \Ak\NovaPoshta\Model\Api\Client */
    protected $_apiClient;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Ak\NovaPoshta\Model\WarehouseFactory $warehouseFactory,
        \Ak\NovaPoshta\Model\CityFactory $cityFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Logger\AdapterFactory $logFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_scopeConfig      = $scopeConfig;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_cityFactory      = $cityFactory;
        $this->_logFactory       = $logFactory;
        $this->_checkoutSession  = $checkoutSession;
        $this->_objectManager    = $objectManager;

        parent::__construct($context);
    }

    /**
     * @return \Ak\NovaPoshta\Model\Api\Client
     */
    public function getApi()
    {
        if ($this->_apiClient === null)
        {
            $this->_apiClient = $this->_objectManager->create('Ak\NovaPoshta\Model\Api\Client');
        }

        return $this->_apiClient;
    }

    /**
     * @param $string
     *
     * @return \Ak\NovaPoshta\Helper\Data
     */
    public function log($string)
    {
        if ($this->getStoreConfig('enable_log')) {
            $this->_logFactory->create(
                ['fileName' => $this->_logFile]
            )->log(
                $string
            );
        }
        return $this;
    }

    /**
     * @param string $key
     * @param null $storeId
     *
     * @return mixed
     */
    public function getStoreConfig($key, $storeId = null)
    {
        return $this->_scopeConfig->getValue(
            "carriers/novaposhta/$key",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return \Ak\NovaPoshta\Model\City
     *
     * @throws \Magento\Framework\Model\Exception
     */
    public function getSenderCity()
    {
        if (is_null($this->_senderCity)) {
            $id = $this->getStoreConfig('sender_city');
            $this->_senderCity = $this->_cityFactory->create()->load($this->getStoreConfig('sender_city'));
            if (!$this->_senderCity->getId()) {
                throw new \Magento\Framework\Model\Exception(__('Store city not defined.'));
            }
        }

        return $this->_senderCity;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\Date
     */
    public function getDeliveryDate()
    {
        $date = new \Magento\Framework\Stdlib\DateTime\Date(
            null,
            DateTime::DATETIME_INTERNAL_FORMAT
        );
        $date->addDay(intval($this->getStoreConfig('shipping_offset')));

        return $date;
    }

    /**
     * @return float
     */
    public function getDefaultPackageWeight()
    {
        return (float) $this->getStoreConfig('default_weight');
    }

    /**
     * @return int
     */
    public function getDefaultPackageLength()
    {
        return (int) $this->getStoreConfig('default_length');
    }

    /**
     * @return int
     */
    public function getDefaultPackageWidth()
    {
        return (int) $this->getStoreConfig('default_width');
    }

    /**
     * @return int
     */
    public function getDefaultPackageHeight()
    {
        return (int) $this->getStoreConfig('default_height');
    }

    /**
     * @param $destinationWarehouseId
     * @return array
     */
    public function getShippingCost($destinationWarehouseId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote                = $this->_checkoutSession->getQuote();

        /** @var \Ak\NovaPoshta\Model\Warehouse $destinationWarehouse */
        $destinationWarehouse = $this->_warehouseFactory->create()->load($destinationWarehouseId);

        /** @var \Ak\NovaPoshta\Model\City $senderCity */
        $senderCity           = $this->getSenderCity();

        /** @var \Ak\NovaPoshta\Model\City $senderCity */
        $destinationCity      = $destinationWarehouse->getCity();

        /** @var \Magento\Framework\Stdlib\DateTime\Date $deliveryDate */
        $deliveryDate         = $this->getDeliveryDate();

        $result = $this->getApi()->getShippingCost($deliveryDate, $senderCity, $destinationCity,
            $this->getDefaultPackageWeight(),
            $this->getDefaultPackageLength(),
            $this->getDefaultPackageWidth(),
            $this->getDefaultPackageHeight(),
            $quote->getSubtotal()
        );

        return $result;
    }
}