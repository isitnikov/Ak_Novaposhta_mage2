<?php
namespace Ak\Novaposhta\Block\Adminhtml;

class Warehouses extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller     = 'adminhtml';
        $this->_blockGroup     = 'Ak_Novaposhta';
        $this->_headerText     = __('Warehouses');
        $this->_addButtonLabel = __('Synchronize with API');

        parent::_construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/synchronize');
    }
}
