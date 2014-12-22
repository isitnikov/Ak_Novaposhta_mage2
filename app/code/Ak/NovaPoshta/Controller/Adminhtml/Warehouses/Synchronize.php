<?php
namespace Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

use Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

class Synchronize extends Warehouses
{
    /** @var \Ak\NovaPoshta\Model\Import */
    protected $_import;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ak\NovaPoshta\Model\Import $import
    ) {
        parent::__construct($context);
        $this->_import = $import;
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ak_NovaPoshta::novaposhta_warehouses_synchronize');
    }

    public function execute()
    {
        try {
            $this->_import->run();
            $this->messageManager->addSuccess(__('City and Warehouse API synchronization finished'));
        }
        catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError(__('Error during synchronization: %s', $e->getMessage()));
        }

        $this->_redirect('*/*/index');

        return $this;
    }
}
