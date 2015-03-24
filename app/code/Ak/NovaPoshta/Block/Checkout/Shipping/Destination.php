<?php

namespace Ak\Novaposhta\Block\Checkout\Shipping;

class Destination extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    /** @var \Ak\Novaposhta\Model\WarehouseFactory */
    protected $_warehouseFactory;

    /** @var \Ak\Novaposhta\Model\Resource\City\Collection */
    protected $_cityCollection;

    /** @var \Ak\Novaposhta\Model\Resource\Warehouse\Collection */
    protected $_warehouseCollection;

    /**
     * @param \Ak\Novaposhta\Model\WarehouseFactory $warehouseFactory
     * @param \Ak\Novaposhta\Model\Resource\City\Collection $cityCollection
     * @param \Ak\Novaposhta\Model\Resource\Warehouse\Collection $warehouseCollection
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Ak\Novaposhta\Model\WarehouseFactory $warehouseFactory,
        \Ak\Novaposhta\Model\Resource\City\Collection $cityCollection,
        \Ak\Novaposhta\Model\Resource\Warehouse\Collection $warehouseCollection,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_warehouseFactory    = $warehouseFactory;
        $this->_cityCollection      = $cityCollection;
        $this->_warehouseCollection = $warehouseCollection;
        $this->_checkoutSession     = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * @return \Ak\NovaPoshta\Model\Warehouse|bool
     */
    public function getWarehouse()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_checkoutSession->getQuote();
        $warehouseId = $quote->getShippingAddress()->getData('warehouse_id');
        if ($warehouseId) {
            /** @var \Ak\Novaposhta\Model\Warehouse $warehouse */
            $warehouse = $this->_warehouseFactory->create()->load($warehouseId);
            if ($warehouse->getId()) {
                return $warehouse;
            }
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getCityId()
    {
        $cityId = (int) $this->getData('city_id');
        if (!$cityId) {
            $warehouse = $this->getWarehouse();
            if ($warehouse) {
                $cityId =  $warehouse->getCity()->getId();;
                $this->setData('city_id', $cityId);
            }
        }

        if ($cityId) {
            return $cityId;
        }

        return false;
    }

    /**
     * @return \Ak\NovaPoshta\Model\Resource\City\Collection
     */
    public function getCities()
    {
        $collection = $this->_cityCollection
            ->setOrder('name_ru');

        return $collection;
    }

    /**
     * @return \Ak\NovaPoshta\Model\Resource\Warehouse\Collection|bool
     */
    public function getWarehouses()
    {
        if ($cityId = $this->getCityId()) {
            /** @var \Ak\NovaPoshta\Model\Resource\Warehouse\Collection $collection */
            $collection = $this->_warehouseCollection;
            $collection->addFieldToFilter('city_id', $cityId);
            $collection->setOrder('address_ru');

            return $collection;
        }

        return false;
    }
}
