<?php
namespace Ak\Novaposhta\Block\Adminhtml\Warehouses;

use Magento\Backend\Block\Widget\Grid as WidgetGrid;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Ak\NovaPoshta\Model\Resource\Warehouse\Collection
     */
    protected $_warehouseCollection;

    /**
     * @var \Ak\NovaPoshta\Model\Resource\City\Collection
     */
    protected $_cityCollection;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ak\NovaPoshta\Model\Resource\Warehouse\Collection $warehouseCollection,
        \Ak\NovaPoshta\Model\Resource\City\Collection $cityCollection,
        array $data = []
    ) {
        $this->_warehouseCollection = $warehouseCollection;
        $this->_cityCollection = $cityCollection;

        parent::__construct($context, $backendHelper, $data);
        $this->setEmptyText(__('No Warehouses Found'));

        $this->setDefaultSort('city_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return WidgetGrid
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_warehouseCollection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'address_ru',
            [
                'header' => __('Address'),
                'index' => 'address_ru',
                'header_css_class' => 'col-template',
                'column_css_class' => 'col-template'
            ]
        );

        $this->addColumn(
            'city_id',
            [
                'header' => __('City'),
                'index' => 'city_id',
                'type' => 'options',
                'options' => $this->_cityCollection->getOptionArray(),
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
        );

        $this->addColumn(
            'phone',
            [
                'header' => __('Phone'),
                'index' => 'phone',
                'header_css_class' => 'col-template',
                'column_css_class' => 'col-template'
            ]
        );

        $this->addColumn(
            'max_weight_allowed',
            [
                'header' => __('Max Weight'),
                'index' => 'max_weight_allowed',
                'header_css_class' => 'col-template',
                'column_css_class' => 'col-template'
            ]
        );

        return $this;
    }
}
