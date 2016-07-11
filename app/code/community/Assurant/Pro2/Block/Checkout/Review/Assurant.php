<?php
class Assurant_Pro2_Block_Checkout_Review_Assurant extends Mage_Core_Block_Template {

    protected $attribute_set_id;

    public function __construct()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $this->_attribute_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);
    }

    /**
     * check to see if aggrement text can be shown at review or not. if there is one product that has protection plan, show it
     *
     * @author atheotsky
     */
    public function canShowAgreenment()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProduct()->getAssurantProductCollection()->count()) {
                return true;
            }
        }

        return false;
    }

    /**
     * get plans for products that don't have a plan
     *
     * @author atheotsky
     */
    public function getAvaialbleProducts()
    {
        $products = array();
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        foreach ($quote->getAllItems() as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $quoteItem = Mage::getModel('sales/quote_item')->load($item->getAssurantItemId());
            $product->setAssurantStamp($item->getBuyRequest()->getData('assurant_stamp'));
            $product->setAssurantItemOptionId($quoteItem->getAssurantItemOptionId());
            $product->setAssurantItemId($item->getAssurantItemId());
            $product->setQuoteItemId($item->getId());

            if ($product->getAttributeSetId() != $this->_attribute_set_id) {
                $products[] = $product;
            }
        }
        return $products;
    }

    /**
     * get first Assurant Item linked to current product
     *
     * @author atheotsky
     */
    public function getAssurantItem($product)
    {
        // get by $assurant_item_id first to handle price change of product at review step
        if ($product->getAssurantItemId()) {
            $product_id = Mage::getModel('sales/quote_item')->load($product->getAssurantItemId())->getProductId();
            return Mage::getModel('catalog/product')->load($product_id);
        }
        return $product->getAssurantProductCollection()->getFirstItem();
    }

    /**
     * get default Plan of assurant item
     */
    public function getDefaultPlan($product, $default_option_id = null)
    {
        if ($product && $product->getId()) {
            $product = Mage::getModel('catalog/product')->load($product->getId());
            $default_option_id = empty($default_option_id) ? $product->getDefaultOptionId() : $default_option_id;

            foreach (unserialize($product->getOptionsHash()) as $plan) {
                if ($plan->id == $default_option_id) {
                    return $plan;
                }
            }
        }

        return false;
    }

    /**
     * get all protection plans of a product
     *
     * @author atheotsky
     */
    public function getProductPlans($product) {
        $product = Mage::getModel('catalog/product')->load($product->getId());
        $options = unserialize($product->getOptionsHash());
        return $options;
    }

    /**
     * get Assurant Plan name by period
     */
    public function getPlanName($p)
    {
        if ($p) {
            $years = $p->period/12;
            if (is_float($years)) $years = number_format($years, 1);
            $years = $years ? "{$years}-Year" : '';
        }

        return " - Assurant&reg; {$years} product protection plan";
    }
}
