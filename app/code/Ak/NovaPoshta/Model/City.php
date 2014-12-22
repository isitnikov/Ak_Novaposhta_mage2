<?php
namespace Ak\NovaPoshta\Model;

class City extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init('Ak\NovaPoshta\Model\Resource\City');
    }
}
