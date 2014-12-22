<?php
namespace Ak\NovaPoshta\Model\Resource\City;

use \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;
use \Magento\Framework\Data\Collection\Db;

/**
 * City collection
 *
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model and model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ak\NovaPoshta\Model\City', 'Ak\NovaPoshta\Model\Resource\City');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->setOrder('name_ru', Db::SORT_ORDER_ASC)->_toOptionArray('id', 'name_ru');
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $v) {
            $array[$v['value']] = $v['label'];
        }

        return $array;
    }
}
