<?php
namespace Ak\NovaPoshta\Model\System\Config\Source;

class City extends \Ak\NovaPoshta\Model\System\Config\City
{
    public function toOptionArray()
    {
        $options = $this->_cityCollection->toOptionArray();

        array_unshift($options, array(
            'value' => '',
            'label' => __('-- Please Select --')
        ));

        return $options;
    }
}
