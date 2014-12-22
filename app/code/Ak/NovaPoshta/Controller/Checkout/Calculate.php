<?php
namespace Ak\NovaPoshta\Controller\Checkout;

class Calculate extends \Ak\NovaPoshta\Controller\Checkout
{
    /** @var \Ak\NovaPoshta\Helper\Data */
    protected $_helper;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /** @var \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency */
    protected $_priceCurrency;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ak\NovaPoshta\Helper\Data $helper,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);

        $this->_helper        = $helper;
        $this->_coreHelper    = $coreHelper;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * Calculate shipping cost for destination
     */
    public function execute()
    {
        $warehouseId    = (int) $this->getRequest()->getParam('warehouse');
        $result         = $this->_helper->getShippingCost($warehouseId);
        $result['cost'] = $this->_priceCurrency->convertAndFormat((float) $result['cost'], false);

        $this->getResponse()->representJson(
            $this->_coreHelper->jsonEncode($result)
        );
    }
}
