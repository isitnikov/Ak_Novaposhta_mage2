<?php
namespace Ak\NovaPoshta\Model\Resource\Warehouse;

use \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

/**
 * Warehouse collection
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
        $this->_init('Ak\NovaPoshta\Model\Warehouse', 'Ak\NovaPoshta\Model\Resource\Warehouse');
    }
}
