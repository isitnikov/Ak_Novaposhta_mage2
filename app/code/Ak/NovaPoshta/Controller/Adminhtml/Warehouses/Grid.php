<?php
namespace Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

use Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

class Grid extends Warehouses
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
