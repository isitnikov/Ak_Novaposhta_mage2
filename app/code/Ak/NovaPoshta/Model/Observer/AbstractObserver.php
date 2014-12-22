<?php
namespace Ak\NovaPoshta\Model\Observer;

abstract class AbstractObserver
{
    /** @var \Magento\Framework\App\Resource */
    protected $_resource;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    protected $_connection;

    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    protected function _getSelect()
    {
        return $this->_getConnection()->select();
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection('write');
        }
        return $this->_connection;
    }

    /**
     * @return \Magento\Framework\App\Resource
     */
    protected function _getResource()
    {
        return $this->_resource;
    }
}