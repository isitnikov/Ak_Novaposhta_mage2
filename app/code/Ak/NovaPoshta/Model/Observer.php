<?php
namespace Ak\NovaPoshta\Model;

class Observer extends \Ak\NovaPoshta\Model\Observer\AbstractObserver
{
    /** @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    /** @var WarehouseFactory */
    protected $_warehouseFactory;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Resource $resource,
        WarehouseFactory $warehouseFactory
    ) {
        parent::__construct($resource);
        $this->_checkoutSession  = $checkoutSession;
        $this->_warehouseFactory = $warehouseFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function loadQuoteAddressCollectionData(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Resource\Quote\Address\Collection $addressCollection */
        $addressCollection = $observer->getData('quote_address_collection');

        $resource = $this->_getResource();
        $connection = $this->_getConnection();
        $select = $this->_getSelect();
        $select->from($resource->getTableName('novaposhta_quote_address'));
        $select->where('address_id IN (?)', $addressCollection->getAllIds());

        foreach ($connection->fetchAll($select) as $row) {
            $addressId = $row['address_id'];
            unset($row['address_id']);
            $addressCollection->getItemById($addressId)->addData($row);
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function loadQuoteAddressData(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Quote\Address $address */
        $address = $observer->getData('quote_address');

        $resource = $this->_getResource();
        $connection = $this->_getConnection();
        $select = $this->_getSelect();
        $select->from($resource->getTableName('novaposhta_quote_address'));
        $select->where('address_id = ?', $address->getId());

        if ($data = $connection->fetchRow($select)) {
            unset($data['address_id']);
            $address->addData($data);
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function saveQuoteAddressData(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Quote\Address $address */
        $address = $observer->getData('quote_address');

        $resource = $this->_getResource();
        $connection = $this->_getConnection();
        $warehouseId = $address->getData('warehouse_id');
        $warehouseLabel = '';

        if ($warehouseId) {
            /** @var \Ak\NovaPoshta\Model\Warehouse $warehouse */
            $warehouse = $this->_warehouseFactory->create()->load($warehouseId);
            $warehouseLabel = $warehouse->getCity()->getData('name_ru')
                . ', ' . $warehouse->getData('address_ru')
                . ', ' . $warehouse->getData('phone');
        }

        $data = array(
            'address_id' => $address->getId(),
            'warehouse_id' => $warehouseId,
            'warehouse_label' => $warehouseLabel,
        );

        $tableName = $resource->getTableName('novaposhta_quote_address');

        if ($data['warehouse_id'] || $data['warehouse_label']) {
            $connection->insertOnDuplicate($tableName, $data);
        } else {
            $connection->delete($tableName, sprintf('address_id = %d', $data['address_id']));
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function loadOrderAddressCollectionData(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Resource\Order\Address\Collection $addressCollection */
        $addressCollection = $observer->getData('order_address_collection');

        $resource = $this->_getResource();
        $connection = $this->_getConnection();
        $select = $this->_getSelect();
        $select->from($resource->getTableName('novaposhta_order_address'));
        $select->where('address_id IN (?)', $addressCollection->getAllIds());

        foreach ($connection->fetchAll($select) as $row) {
            $addressId = $row['address_id'];
            unset($row['address_id']);
            $addressCollection->getItemById($addressId)->addData($row);
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function loadOrderAddressData(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $observer->getData('address');

        $resource = $this->_getResource();
        $connection = $this->_getConnection();
        $select = $this->_getSelect();
        $select->from($resource->getTableName('novaposhta_order_address'));
        $select->where('address_id = ?', $address->getId());

        if ($data = $connection->fetchRow($select)) {
            unset($data['address_id']);
            $address->addData($data);
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function saveOrderAddressData(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $observer->getData('address');

        $resource = $this->_getResource();
        $connection = $this->_getConnection();
        $warehouseId = $address->getData('warehouse_id');
        $warehouseLabel = '';

        if ($warehouseId) {
            /** @var \Ak\NovaPoshta\Model\Warehouse $warehouse */
            $warehouse = $this->_warehouseFactory->create()->load($warehouseId);
            $warehouseLabel = $warehouse->getCity()->getData('name_ru')
                . ', ' . $warehouse->getData('address_ru')
                . ', ' . $warehouse->getData('phone');
        }

        $data = array(
            'address_id' => $address->getId(),
            'warehouse_id' => $warehouseId,
            'warehouse_label' => $warehouseLabel,
        );

        $tableName = $resource->getTableName('novaposhta_order_address');

        if ($data['warehouse_id'] || $data['warehouse_label']) {
            $connection->insertOnDuplicate($tableName, $data);
        } else {
            $connection->delete($tableName, sprintf('address_id = %d', $data['address_id']));
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function saveOrderData(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');

        if ($warehouseLabel = $order->getShippingAddress()->getData('warehouse_label')) {
            $shippingDescription = $order->getData('shipping_description');
            $order->setData('shipping_description', $shippingDescription . PHP_EOL . " ({$warehouseLabel}) ");
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Model\Exception
     */
    public function saveShippingMethodBefore(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getControllerAction();

        if (preg_match('/^novaposhta_type_\d+$/i', $controller->getRequest()->getParam('shipping_method'))) {
            /** @var \Ak\NovaPoshta\Model\Warehouse $warehouse */
            $warehouse = $this->_warehouseFactory->create()
                ->load($controller->getRequest()->getParam('novaposhta_warehouse'));
            if (!$warehouse->getId()) {
                throw new \Magento\Framework\Model\Exception(__('Invalid Warehouse.'));
            }
            /** @var \Magento\Sales\Model\Quote $quote */
            $quote = $this->_checkoutSession->getQuote();
            $quote->getShippingAddress()->setData('warehouse_id', $warehouse->getId());
        }
    }
}