<?php
namespace Ak\Novaposhta\Helper;

use Magento\Framework\Stdlib\DateTime;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Log adapter factory
     *
     * @var \Ak\NovaPoshta\Model\Log\LoggerInterface
     */
    protected $_logger;

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

    /** @var DateTime\DateTimeFactory */
    protected $dateTime;

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
        \Ak\NovaPoshta\Model\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        DateTime\DateTimeFactory $dateTime
    ) {
        $this->_scopeConfig      = $scopeConfig;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_cityFactory      = $cityFactory;
        $this->_logger           = $logger;
        $this->_checkoutSession  = $checkoutSession;
        $this->_objectManager    = $objectManager;
        $this->dateTime          = $dateTime;

        parent::__construct($context);
    }

    /**
     * @return \Ak\NovaPoshta\Model\Api\Client
     */
    public function getApi()
    {
        if ($this->_apiClient === null) {
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
            $this->_logger->info($string);
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
     * @throws \Magento\Framework\Exception
     */
    public function getSenderCity()
    {
        if (is_null($this->_senderCity)) {
            $id = $this->getStoreConfig('sender_city');
            $this->_senderCity = $this->_cityFactory->create()->load($this->getStoreConfig('sender_city'));
            if (!$this->_senderCity->getId()) {
                throw new \Magento\Framework\Exception(__('Store city not defined.'));
            }
        }

        return $this->_senderCity;
    }

    /**
     * @return string
     */
    public function getDeliveryDate()
    {
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $date */
        $date = $this->dateTime->create();

        return $date->date(
            \Ak\NovaPoshta\Model\Api\Client::DATE_FORMAT,
            sprintf('%s +%d day', $date->gmtDate(), $this->getStoreConfig('shipping_offset'))
        );
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
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote                = $this->_checkoutSession->getQuote();

        /** @var \Ak\NovaPoshta\Model\Warehouse $destinationWarehouse */
        $destinationWarehouse = $this->_warehouseFactory->create()->load($destinationWarehouseId);

        /** @var \Ak\NovaPoshta\Model\City $senderCity */
        $senderCity           = $this->getSenderCity();

        /** @var \Ak\NovaPoshta\Model\City $senderCity */
        $destinationCity      = $destinationWarehouse->getCity();

        /** @var string $deliveryDate */
        $deliveryDate         = $this->getDeliveryDate();

        $result = $this->_apiClient->getShippingCost($deliveryDate, $senderCity, $destinationCity,
            $this->getDefaultPackageWeight(),
            $this->getDefaultPackageLength(),
            $this->getDefaultPackageWidth(),
            $this->getDefaultPackageHeight(),
            $quote->getSubtotal()
        );

        return $result;
    }
}