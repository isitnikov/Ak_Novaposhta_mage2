<?php
namespace Ak\NovaPoshta\Model\Carrier;

class NovaPoshta
    extends \Magento\Shipping\Model\Carrier\AbstractCarrierOnline
    implements \Magento\Shipping\Model\Carrier\CarrierInterface
{

    protected $_code = 'novaposhta';

    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * Carrier helper
     *
     * @var \Ak\NovaPoshta\Helper\Data
     */
    protected $_carrierHelper;

    /** @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Sales\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Ak\NovaPoshta\Helper\Data $carrierHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Sales\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Ak\NovaPoshta\Helper\Data $carrierHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_carrierHelper = $carrierHelper;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logAdapterFactory,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address\RateRequest $request
     * @internal param \Magento\Shipping\Model\Rate\Request $data
     * @return \Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(\Magento\Sales\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var $result \Magento\Shipping\Model\Rate\Result */
        $result = $this->_rateFactory->create();

        $shippingPrice = 0;
        $deliveryType = \Ak\NovaPoshta\Model\Api\Client::DELIVERY_TYPE_WAREHOUSE_WAREHOUSE;

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->_checkoutSession->getQuote();
        $warehouseId = $quote->getShippingAddress()->getData('warehouse_id');

        if ($warehouseId) {
            $shippingCost = $this->_carrierHelper->getShippingCost($warehouseId, false);
            $shippingPrice = $shippingCost['cost'];
        }

        /** @var $method \Magento\Sales\Model\Quote\Address\RateResult\Method */
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code)
            ->setCarrierTitle($this->getConfigData('name'))
            ->setMethod('type_' . $deliveryType)
            ->setMethodTitle(__('Delivery to Nova Poshta warehouse'))
            ->setPrice($shippingPrice)
            ->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @return array
     */
    protected function _getWeightPriceMap()
    {
        $weightPriceMap = $this->getConfigData('weight_price');
        if (empty($weightPriceMap)) {
            return array();
        }

        return unserialize($weightPriceMap);
    }

    /**
     * @param $packageWeight
     *
     * @return float
     */
    protected function _getDeliveryPriceByWeight($packageWeight)
    {
        $weightPriceMap = $this->_getWeightPriceMap();
        $resultingPrice = 0.00;
        if (empty($weightPriceMap)) {
            return $resultingPrice;
        }

        $minimumWeight = 1000000000;
        foreach ($weightPriceMap as $weightPrice) {
            if ($packageWeight <= $weightPrice['weight'] && $weightPrice['weight'] <= $minimumWeight) {
                $minimumWeight = $weightPrice['weight'];
                $resultingPrice = $weightPrice['price'];
            }
        }

        return $resultingPrice;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\Object $request
     * @return \Magento\Framework\Object
     */
    protected function _doShipmentRequest(\Magento\Framework\Object $request)
    {
        return $this;
    }
}
