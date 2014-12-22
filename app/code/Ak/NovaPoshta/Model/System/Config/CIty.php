<?php
namespace Ak\NovaPoshta\Model\System\Config;

class City implements \Magento\Framework\Option\ArrayInterface
{
    /** @var \Ak\NovaPoshta\Model\Resource\City\Collection */
    protected $_cityCollection;

    public function __construct(\Ak\NovaPoshta\Model\Resource\City\Collection $cityCollection)
    {
        $this->_cityCollection = $cityCollection;
    }

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
