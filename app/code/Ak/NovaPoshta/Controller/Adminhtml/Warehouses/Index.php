<?php
namespace Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

use Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

class Index extends Warehouses
{
    public function execute()
    {
//        if ($this->getRequest()->getQuery('ajax')) {
//            $this->_forward('grid');
//            return;
//        }
        $this->_view->loadLayout();
        $this->_setActiveMenu('Ak_NovaPoshta::novaposhta_warehouses');
        $this->_addBreadcrumb(__('Warehouses'), __('Warehouses'));
        $this->_addContent(
            $this->_view->getLayout()->createBlock('Ak\NovaPoshta\Block\Adminhtml\Warehouses', 'warehouses')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Warehouses'));
        $this->_view->renderLayout();
    }
}
