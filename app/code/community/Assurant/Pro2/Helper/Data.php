<?php
class Assurant_Pro2_Helper_Data extends Mage_Core_Helper_Abstract 
{
	/**
     * add Assurant Products to Cart Widgets info
     *
     * @author PR
     */
	public function cartAssurant($_item) 
	{
		$data = array();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
		$cartConfigValue = Mage::getStoreConfig('aintegration/touchpoints/cart', Mage::app()->getStore());
		if(!$cartConfigValue){ return $data; }
		if($_item->getProductId()){
			$product = Mage::getModel('catalog/product')->load($_item->getProductId());

			$attributeSetName = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId())->getAttributeSetName();
			if(Assurant_Pro2_Model_Aproduct::SET_NAME == $attributeSetName){ return $data; }

			$assurantItem = $product->getAssurantProductCollection()
	            ->setPositionOrder()
	            ->addAttributeToSelect('*')->getFirstItem();
	        if(!count($assurantItem->getData())){ return $data; }

            $options = array();
            foreach (unserialize($assurantItem->getOptionsHash()) as $plan) {
                $value = $plan->period/12;
                if (is_float($value)) $value = number_format($value, 1);
                $value = $value ? "{$value}-Year" : '';

                $routeParams = array(
                    'current_id' => $_item->getProductId(),
                    'assurant_id' => $assurantItem->getId(),
                    'assurant_stamp' => $_item->getBuyRequest()->getData('assurant_stamp'), // add assurant_stamp to request param to identify cart items of same product
                    'option_id' => $plan->id
                );
                $addCoverageUrl = Mage::getUrl('pro2/submit/addcoverage', $routeParams);
                $options[] = array(
                    'default' => $assurantItem->getDefaultOptionId() == $plan->id,
                    'value' => $value,
                    'price' => htmlentities(Mage::helper('checkout')->formatPrice($plan->price)),
                    'option_id' => $plan->id,
                    'redirect' => $addCoverageUrl
                );
            }

	        if($assurantItem){
	        	$data['sku'] = $assurantItem->getSku();
	        	$data['name'] = $assurantItem->getName();
	        	$data['price'] = $assurantItem->getPrice();
	        	$data['default_option_id'] = $assurantItem->getDefaultOptionId();
	        	$data['default_price'] = Mage::getModel('pro2/observer')->getAssurantPlanPrice($assurantItem, $assurantItem->getDefaultOptionId());
	        	$data['options'] = $options;
	        }
	    }
	    return $data;
    }

    /**
     * check is Assurant Product
     *
     * @author PR
     */
    public function isAssurantProduct($productId) 
	{
		if($productId){
			$product = Mage::getModel('catalog/product')->load($productId);
			$attributeSetName = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId())->getAttributeSetName();
			if(Assurant_Pro2_Model_Aproduct::SET_NAME == $attributeSetName){ 
				return true; 
			}
	    }
	    return false;
    }

    /**
     * load Script Js if touchpoints enabled. also need to check if current product has assurant plans or not
     *
     * @author atheotsky
     */
    public function scriptJs() 
    {
        if( Mage::registry('current_product')->getAssurantProductCollection()->count() > 0
            && (Mage::getStoreConfig('aintegration/touchpoints/interstitial', Mage::app()->getStore())
            || Mage::getStoreConfig('aintegration/touchpoints/product_page', Mage::app()->getStore()))) {

            return 'pro2/js/script.js';
        }

        return '';
    }

    /**
     * load widget template view file
     *
     * @patchedby atheotsky
     */
    public function widgetView() 
    {
        if( Mage::registry('current_product')->getAssurantProductCollection()->count() > 0 
            && (Mage::getStoreConfig('aintegration/touchpoints/product_page', Mage::app()->getStore())
            || Mage::getStoreConfig('aintegration/touchpoints/interstitial', Mage::app()->getStore()))) {
            return 'pro2/view.phtml';
        }

        return '';
    }

    /**
     * load custom template if touchpoints enabled for checkout page
     *
     * @author atheotsky
     */
    public function cartItemTemplate()
    {
        if(Mage::getStoreConfig('aintegration/touchpoints/cart', Mage::app()->getStore())) {
	    	return 'pro2/checkout/cart/item/default.phtml';
	    }

        return 'checkout/cart/item/default.phtml';
    }

    /**
     * load tab template if touchpoints enabled
     *
     * @author PR
     */
    public function assurantTab() 
	{
	    if(Mage::getStoreConfig('aintegration/touchpoints/product_page', Mage::app()->getStore())){
	    	return 'pro2/tabs.phtml';
	    }else{
	    	return '';
	    }
    }

    /**
     * load checkout template if touchpoints enabled
     *
     * @author PR
     */
    public function assurantCheckout() 
	{
	    if(Mage::getStoreConfig('aintegration/touchpoints/checkout', Mage::app()->getStore())){
	    	return 'pro2/checkout/onepage/review/assurant.phtml';
	    }

        return '';
    }

    /**
     * load checkout javascript loader if touchpoints enabled
     *
     * @author atheotsky
     */
    public function assurantCheckoutJsLoader() 
	{
	    if(Mage::getStoreConfig('aintegration/touchpoints/checkout', Mage::app()->getStore())){
	    	return 'pro2/checkout/onepage/review/js_loader.phtml';
	    }

        return '';
    }

    /**
     * load checkout css if touchpoints enabled
     *
     * @author PR
     */
    public function checkoutCss() 
    {
        if(Mage::getStoreConfig('aintegration/touchpoints/checkout', Mage::app()->getStore())){
            return 'pro2/css/checkoutstyle.css';
        }else{
            return '';
        }
    }


    /**
     * load checkout Js if touchpoints enabled
     *
     * @author PR
     */
    public function checkoutJs() 
    {
        if(Mage::getStoreConfig('aintegration/touchpoints/product_page', Mage::app()->getStore()) && Mage::getStoreConfig('aintegration/touchpoints/interstitial', Mage::app()->getStore()) || Mage::getStoreConfig('aintegration/touchpoints/checkout', Mage::app()->getStore())){
            return 'pro2/js/checkoutscript.js';
        }else{
            return '';
        }
    }

    /**
     * add Assurant Item To Cart with Custom name, price and option
     *
     * @param $parent_product
     * @param $assurant_product_id
     * @param $option_id
     *
     * @author atheotsky
     */
    public function addAssurantProduct($parent_product, $assurant_product_id, $option_id = null)
    {
        if(empty($assurant_product_id)) return;

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $current_item = $quote->getItemByProduct($parent_product);
        $assurant_product = Mage::getModel('catalog/product')->load($assurant_product_id);
        $option_id = empty($option_id) ? $assurant_product->getDefaultOptionId() : $option_id;

        if($current_item->getAssurantItemId()){
            $quoteItem = Mage::getModel('sales/quote_item')->load($current_item->getAssurantItemId());
            $quoteItem->setProduct($assurant_product);
        }
        else {
            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($assurant_product);
        }

        /*add custom price*/
        if ($custom_price = $this->getAssurantPlanPrice($assurant_product, $option_id)) {
            $quoteItem->setCustomPrice($custom_price);
            $quoteItem->setOriginalCustomPrice($custom_price);
        }

        $quoteItem->setAssurantItemOptionId($option_id);
        $quoteItem->setStoreId(Mage::app()->getStore()->getId());
        $quoteItem->setQuote($quote);
        $quoteItem->setQty(intval($current_item->getQty()));
        $quoteItem->save();
        $current_item->setAssurantItemId($quoteItem->getId());

        $quote->addItem($quoteItem);
        $quote->save();
    }

    /**
     * get custom Plan Price to set custom price for quote item
     *
     * @author atheotsky
     */
    public function getAssurantPlanPrice($product, $option_id)
    {
        if ($hash = $product->getOptionsHash()) {
            $plans = unserialize($hash);
            foreach ($plans as $p) {
                if ($p->id == $option_id) {
                    return $p->price;
                }
            }
        }
        return false;
    }
}
