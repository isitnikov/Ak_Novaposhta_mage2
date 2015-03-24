<?php
namespace Ak\NovaPoshta\Controller\Checkout;

use Magento\Framework\Controller\Result\LayoutFactory as ResultLayoutFactory;

class Form extends \Ak\NovaPoshta\Controller\Checkout
{
    /** @var ResultLayoutFactory */
    protected $resultLayoutFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ResultLayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Render form for choose city and warehouse
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $result */
        $result = $this->resultLayoutFactory->create();

        if ($cityId = $this->getRequest()->getParam('city')) {
            /** @var \Ak\Novaposhta\Block\Checkout\Shipping\Destination $block */
            $block = $result->getLayout()->getBlock('novaposhta.checkout.shipping.destination');
            $block->setCityId($cityId);
        }

        return $result;
    }
}
