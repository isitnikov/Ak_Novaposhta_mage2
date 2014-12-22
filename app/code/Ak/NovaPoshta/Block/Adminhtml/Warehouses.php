<?php
namespace Ak\Novaposhta\Block\Adminhtml;

class Warehouses extends \Magento\Backend\Block\Template
{
    protected $_template = 'warehouses.phtml';

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'synchronize',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Synchronize with API'),
                'onclick' => "window.location='" . $this->getSynchronizeUrl() . "'",
                'class' => 'add primary add-template'
            ]
        );

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Ak\NovaPoshta\Block\Adminhtml\Warehouses\Grid',
                'novaposhta.warehouses.grid'
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Get the url for create
     *
     * @return string
     */
    public function getSynchronizeUrl()
    {
        return $this->getUrl('*/*/synchronize');
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Manage warehouses');
    }
}
