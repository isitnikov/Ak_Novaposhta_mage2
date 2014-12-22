<?php
namespace Ak\NovaPoshta\Model;

class Import extends Observer\AbstractObserver
{
    /** @var  array */
    protected $_existingCities;

    /** @var  array */
    protected $_existingWarehouses;

    /** @var array */
    protected $_dataMapCity = array(
        'id'      => 'id',
        'nameRu'  => 'name_ru',
        'nameUkr' => 'name_ua'
    );

    /** @var array */
    protected $_dataMapWarehouse = array(
        'wareId'                   => 'id',
        'city_id'                  => 'city_id',
        'address'                  => 'address_ua',
        'addressRu'                => 'address_ru',
        'phone'                    => 'phone',
        'weekday_work_hours'       => 'weekday_work_hours',
        'weekday_reseiving_hours'  => 'weekday_reseiving_hours',
        'weekday_delivery_hours'   => 'weekday_delivery_hours',
        'saturday_work_hours'      => 'saturday_work_hours',
        'saturday_reseiving_hours' => 'saturday_reseiving_hours',
        'saturday_delivery_hours'  => 'saturday_delivery_hours',
        'max_weight_allowed'       => 'max_weight_allowed',
        'x'                        => 'longitude',
        'y'                        => 'latitude',
        'number'                   => 'number_in_city'
    );

    /** @var \Ak\NovaPoshta\Helper\Data */
    protected $_helper;

    /** @var \Ak\NovaPoshta\Model\Api\Client */
    protected $_apiClient;

    /** @var \Ak\NovaPoshta\Model\Resource\City\Collection */
    protected $_cityCollection;

    /** @var \Ak\NovaPoshta\Model\Resource\Warehouse\Collection */
    protected $_warehouseCollection;

    /** @var \Magento\Framework\Logger */
    protected $_logger;

    public function __construct(
        \Ak\NovaPoshta\Model\Resource\Warehouse\Collection $warehouseCollection,
        \Ak\NovaPoshta\Model\Resource\City\Collection $cityCollection,
        \Ak\NovaPoshta\Model\Api\Client $apiClient,
        \Ak\NovaPoshta\Helper\Data $helper,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\App\Resource $resource
    ) {
        parent::__construct($resource);

        $this->_warehouseCollection = $warehouseCollection;
        $this->_cityCollection      = $cityCollection;
        $this->_apiClient           = $apiClient;
        $this->_helper              = $helper;
        $this->_logger              = $logger;
    }

    /**
     * @throws \Magento\Framework\Model\Exception
     *
     * @return \Ak\NovaPoshta\Model\Import
     */
    public function run()
    {
        try {
            /** @var $apiClient \Ak\NovaPoshta\Model\Api\Client */
            $apiClient = $this->_apiClient;

            $this->_helper->log('Start city import');
            $cities = $apiClient->getCityWarehouses();
            $this->_importCities($cities);
            $this->_helper->log('End city import');

            $this->_helper->log('Start warehouse import');
            $warehouses = $apiClient->getWarehouses();
            $this->_importWarehouses($warehouses);
            $this->_helper->log('End warehouse import');
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_logger->logException($e);
            $this->_helper->log("Exception: \n" . $e->getMessage());
            throw $e;
        }

        return $this;
    }

    /**
     * @param array $cities
     *
     * @return bool
     *
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _importCities(array $cities)
    {
        if (empty($cities)) {
            $this->_helper->log('No city with warehouses received');
            throw new \Magento\Framework\Model\Exception('No city with warehouses received');
        }

        $connection = $this->_getConnection();
        $tableName  = $connection->getTableName('novaposhta_city');

        $cities = $this->_applyMap($cities, $this->_dataMapCity);

        $existingCities = $this->_getExistingCities();
        $citiesToDelete = array_diff(array_keys($existingCities), array_keys($cities));

        if (count($citiesToDelete) > 0) {
            $connection->delete($tableName, $citiesToDelete);
            $this->_helper->log(sprintf("Warehouses deleted: %s", implode(',', $citiesToDelete)));
        }

        if (count($cities) > 0) {
            $tableName  = $connection->getTableName('novaposhta_city');
            $connection = $this->_getConnection();
            $connection->beginTransaction();
            try {
                foreach ($cities as $data) {
                    $connection->insertOnDuplicate($tableName, $data);
                }
                $connection->commit();
            } catch (\Magento\Framework\Model\Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }

        return true;
    }

    /**
     * @param array $existingCity
     * @param array $city
     *
     * @return bool
     */
    protected function _isCityChanged(array $existingCity, array $city)
    {
        foreach ($existingCity as $key => $value) {
            if (isset($city[$key]) && $city[$key] != $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $apiObjects
     * @param array $map
     * @return array
     */
    protected function _applyMap(array $apiObjects, array $map)
    {
        $resultingArray = array();
        $idKey = array_search('id', $map);
        foreach ($apiObjects as $apiObject) {
            $id = (string) $apiObject->$idKey;
            $resultingArray[$id] = array();
            foreach ($apiObject as $apiKey => $value) {
                if (!isset($map[$apiKey])) {
                    continue;
                }
                $resultingArray[$id][$map[$apiKey]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
            }
        }

        return $resultingArray;
    }

    /**
     * @param array $warehouses
     *
     * @return bool
     *
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _importWarehouses(array $warehouses)
    {
        if (empty($warehouses)) {
            $this->_helper->log(__('No warehouses received'));
            throw new \Magento\Framework\Model\Exception(__('No warehouses received'));
        }

        $warehouses = $this->_applyMap($warehouses, $this->_dataMapWarehouse);
        $existingWarehouses = $this->_getExistingWarehouses();
        $warehousesToDelete = array_diff(array_keys($existingWarehouses), array_keys($warehouses));

        $connection = $this->_getConnection();
        $tableName  = $connection->getTableName('novaposhta_warehouse');

        if (count($warehousesToDelete) > 0) {
            $connection->delete($tableName, $warehousesToDelete);
            $this->_helper->log(sprintf("Warehouses deleted: %s", implode(',', $warehousesToDelete)));
        }

        $connection->beginTransaction();
        try {
            foreach ($warehouses as $data) {
                $connection->insertOnDuplicate($tableName, $data);
            }
            $connection->commit();
        } catch (\Magento\Framework\Model\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @return array
     */
    protected function _getExistingWarehouses()
    {
        if (!$this->_existingWarehouses) {
            /** @var \Ak\NovaPoshta\Model\Resource\Warehouse\Collection $collection */
            $collection = $this->_warehouseCollection;
            $this->_existingWarehouses = $collection->getAllIds();
        }
        return $this->_existingWarehouses;
    }

    /**
     * @return array
     */
    protected function _getExistingCities()
    {
        if (!$this->_existingCities) {
            /** @var \Ak\NovaPoshta\Model\Resource\City\Collection $collection */
            $collection = $this->_cityCollection;
            $this->_existingCities = $collection->getAllIds();
        }
        return $this->_existingCities;
    }
}