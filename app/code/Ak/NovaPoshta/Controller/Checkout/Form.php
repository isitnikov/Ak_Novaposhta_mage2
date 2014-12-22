<?php
namespace Ak\NovaPoshta\Controller\Checkout;

class Form extends \Ak\NovaPoshta\Controller\Checkout
{
    /**
     * Render form for choose city and warehouse
     */
    public function execute()
    {
        $this->_view->loadLayout();
        if ($cityId = $this->getRequest()->getParam('city')) {
            $root = $this->_view->getLayout()->getBlock('novaposhta.checkout.shipping.destination');
            $root->setCityId($cityId);
        }

        $this->_view->renderLayout();
    }
}
