<?php
namespace Ak\NovaPoshta\Model\Resource;

class Warehouse extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('novaposhta_warehouse', 'id');
    }
}
