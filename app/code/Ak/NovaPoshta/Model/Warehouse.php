<?php
namespace Ak\NovaPoshta\Model;

class Warehouse extends \Magento\Framework\Model\AbstractModel
{
    /** @var CityFactory */
    protected $_cityFactory;

    public function __construct(
        \Ak\NovaPoshta\Model\CityFactory $cityFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_cityFactory = $cityFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ak\NovaPoshta\Model\Resource\Warehouse');
    }

    /**
     * @return City
     */
    public function getCity()
    {
        return $this->_cityFactory->create()->load($this->getData('city_id'));
    }
}
