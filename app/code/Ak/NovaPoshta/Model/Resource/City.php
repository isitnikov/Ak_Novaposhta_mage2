<?php
namespace Ak\NovaPoshta\Model\Resource;

class City extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('novaposhta_city', 'id');
    }
}
