<?php
namespace Ak\NovaPoshta\Controller\Adminhtml;

class Warehouses extends \Magento\Backend\App\Action
{
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ak_NovaPoshta::novaposhta_warehouses');
    }
}
