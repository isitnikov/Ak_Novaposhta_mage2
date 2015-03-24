<?php
namespace Ak\NovaPoshta\Controller\Checkout;

use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;

class Calculate extends \Ak\NovaPoshta\Controller\Checkout
{
    /** @var \Ak\NovaPoshta\Helper\Data */
    protected $helper;

    /** @var \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency */
    protected $priceCurrency;

    /** @var ResultJsonFactory */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Ak\NovaPoshta\Helper\Data $helper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param ResultJsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ak\NovaPoshta\Helper\Data $helper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ResultJsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helper            = $helper;
        $this->priceCurrency     = $priceCurrency;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Calculate shipping cost for destination
     *
     * @return array|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $warehouseId    = (int) $this->getRequest()->getParam('warehouse');
        $result         = $this->helper->getShippingCost($warehouseId);
        $result['cost'] = $this->priceCurrency->convertAndFormat((float) $result['cost'], false);

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        $result->setData($result);

        return $result;
    }
}
