<?php
namespace Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

use Ak\NovaPoshta\Controller\Adminhtml\Warehouses;
use Magento\Backend\Model\View\Result\RedirectFactory as ResultRedirectFactory;

class Synchronize extends Warehouses
{
    /** @var \Ak\NovaPoshta\Model\Import */
    protected $import;

    /** @var ResultRedirectFactory */
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ak\NovaPoshta\Model\Import $import,
        ResultRedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->import = $import;
        $this->resultRedirectFactory = $resultRedirectFactory;
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

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Exception
     * @throws \Magento\Framework\Exception
     */
    public function execute()
    {
        try {
            $this->import->run();
            $this->messageManager->addSuccess(__('City and Warehouse API synchronization finished'));
        } catch (\Magento\Framework\Exception $e) {
            $this->messageManager->addError(__('Error during synchronization: %s'), $e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $result */
        $result = $this->resultRedirectFactory->create();
        $result->setPath('*/*/index');

        return $result;
    }
}
