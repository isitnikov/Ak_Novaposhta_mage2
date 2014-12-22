<?php
namespace Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

use Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

class Grid extends Warehouses
{
    public function execute()
    {
        $this->_view->loadLayout();
        $grid = $this->_view->getLayout()->createBlock('Ak\NovaPoshta\Block\Adminhtml\Warehouses\Grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }
}
