<?php
namespace Ak\NovaPoshta\Controller\Adminhtml\Warehouses;

use Ak\NovaPoshta\Controller\Adminhtml\Warehouses;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory as ResultForwardFactory;

class Index extends Warehouses
{
    /** @var ResultPageFactory */
    protected $resultPageFactory;

    /** @var ResultForwardFactory */
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ResultPageFactory $resultPageFactory,
        ResultForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            /** @var \Magento\Backend\Model\View\Result\Forward $result */
            $result = $this->resultForwardFactory->create();
            $result->forward('grid');
            return $result;
        }

        /** @var \Magento\Backend\Model\View\Result\Page $result */
        $result = $this->resultPageFactory->create();
        $result->setActiveMenu('Ak_NovaPoshta::novaposhta_warehouses');
        $result->addBreadcrumb(__('Nova Poshta'), __('Nova Poshta'));
        $result->addBreadcrumb(__('Warehouses'), __('Warehouses'));
        $result->getConfig()->getTitle()->prepend(__('Warehouses'));
        return $result;
    }
}
