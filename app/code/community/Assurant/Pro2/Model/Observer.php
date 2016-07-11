<?php
class Assurant_Pro2_Model_Observer {

    CONST LIMIT_STATE_MSG = 'Thank you for your interest in purchasing Assurant Product Protection coverage for your product. This program is currently not available in the state/country that you selected during the checkout process. We have removed Assurant Product Protection from the checkout. We apologize for any inconvenience this may have caused you.';
    /**
     * Validate state before place order
     *
     * @author atheotsky
     */
    public function validateState(Varien_Event_Observer $observer) {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        /* validate state here */
        if ($this->isAssurantOrder($quote)) {
            $allowed_states = explode(',', Mage::getStoreConfig('aintegration/setting/allowed_states'));
            $order_state = Mage::getModel('directory/region')->load($quote->getShippingAddress()->getRegionId())->getCode();
            if (!in_array($order_state, $allowed_states)) {
                /* throw error message */
                Mage::throwException(self::LIMIT_STATE_MSG);
            }
        }
    }
    /**
     * Post Purchase request to Assurant to create new Insurance plan
     *
     * @author atheotsky
     */
    public function createInsurance(Varien_Event_Observer $observer) {
        $curl = Mage::getModel("pro2/connect_curl");
        $order = $observer->getEvent()->getOrder();
        
        if ($this->isAssurantOrder($order)) {
            $curl->createInsurance($order);
        }
    }
    
    /**
     * check to see if current order has assurant produt or not
     * 
     * @param quote or order
     * @author atheotsky
     */
    private function isAssurantOrder($obj){
        foreach ($obj->getAllItems() as $item) {
            if (Mage::getModel('eav/entity_attribute_set')->load($item->getProduct()->getAttributeSetId())->getAttributeSetName() == Assurant_Pro2_Model_Aproduct::SET_NAME) return true;
        }
        return false;
    }

    /**
     * exclude Assurant products from Product collection
     *
     * @author atheotsky
     */
    public function excludeAssurantProducts($observer) {
        if (!Mage::getStoreConfig('aintegration/setting/hide_assurant_products')) return;

        $session = Mage::getSingleton('core/session');
        $collection = $observer->getCollection();
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $attribute_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);

        // hanlde assurant lock. it's flash variable so we delete it right here
        if ($session->getByPassAssurantLock()) {
            $session->unsByPassAssurantLock();
            return;
        }

        $collection->addAttributeToFilter('attribute_set_id', array('neq' => $attribute_set_id));

        // dispatch new event to override behavior later
        Mage::dispatchEvent('exclude_assurant_products_after', array('collection' => $collection));
    }

    /**
     * lock Assurant Attributes, prevent editing
     *
     * @author atheotsky
     */
    public function lockAttributes($observer) {
        if (!Mage::getStoreConfig('aintegration/setting/hide_assurant_products')) return;

        $event = $observer->getEvent();
        $product = $event->getProduct();

        //check to see if it's Assurant product or not
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $attribute_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);
        if ($product->getAttributeSetId() != $attribute_set_id) return;

        $attributes = array(
            'name',
            'sku',
            'description',
            'short_description',
            'status',
            'visibility',
            'price',
            'avb_url',
            'pib_url',
            'max_period',
            'default_option_id',
            'insurance_company',
            'options_hash',
        );

        foreach ($attributes as $code)
        {
            $product->lockAttribute($code);
        }
    }


    /**
     * save Assurant Products when merchant choose assurant products from proudct edit tab
     *
     * @author atheotsky
     */
    public function catalogProductPrepareSave($observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        $request = $event->getRequest();
        $links = $request->getPost('links');
        if (isset($links['assurant']) && !$product->getAssurantReadonly()) {
            $product->setAssurantLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['assurant']));
        }
    }

    /**
     * duplciate product with assurant matches
     *
     * @author atheotsky
     */
    public function catalogModelProductDuplicate($observer)
    {
        $event = $observer->getEvent();
        $currentProduct = $event->getCurrentProduct();
        $newProduct = $event->getNewProduct();
        $data = array();
        $currentProduct->getLinkInstance()->useAssurantLinks();
        $attributes = array();
        foreach ($currentProduct->getLinkInstance()->getAttributes() as $_attribute) {
            if (isset($_attribute['code'])) {
                $attributes[] = $_attribute['code'];
            }
        }
        foreach ($currentProduct->getAssurantLinkCollection() as $_link) {
            $data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
        }
        $newProduct->setAssurantLinkData($data);
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
            foreach ($plans as $p)
            {
                if ($p->id == $option_id) return $p->price;
            }
        }
        return false;
    }

    /**
     * add Assurant Products to Cart
     *
     * @author atheotsky + PR
     */
    public function addAssurantPlan($observer) 
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        $request = $event->getRequest();

        $assurantProductId = $request->getParam('assurant_product');
        $option_id = null;
        if (strpos($assurantProductId, ':') != false)
        {
            list($assurantProductId, $option_id) = explode(':', $assurantProductId);
        }
      
        Mage::helper('pro2')->addAssurantProduct($product, $assurantProductId, $option_id);
    }

    /**
     * set Assurant Plan Qty by Parent product Qty
     *
     * @author atheotsky
     */
    public function updateAssurantPlanQty($observer) {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        foreach($quote->getAllItems() as $item){
            if(!$item->getAssurantItemId()) continue;

            $plan = $quote->getItemById($item->getAssurantItemId());
            if ($plan && $plan->getId()) {
                $plan->setQty(intval($item->getQty()));
            }
        }
    }

    /**
     * remove Assurant Products on parent product removed
     *
     * @author PR
     */
    public function removeAssurantProduct($observer) 
    {
        $event = $observer->getEvent();
        $item = $event->getQuoteItem();    
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        foreach ($quote->getAllItems() as $allItem) {
            if($allItem->getAssurantItemId() == $item->getId()){
                
                $assurantItem = $quote->getItemById($allItem->getId());
                $assurantItem->setAssurantItemId(null);
            }
        }

        if($item->getAssurantItemId()){
            $quote->removeItem($item->getAssurantItemId())->save();
        }
    }

    /**
     * correct Assuran Protection plan for product. it check for product price change and qty change
     *
     * @author atheotsky
     */
    public function correctAssurantPlan($observer)
    {
        $event = $observer->getEvent();
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if ($item->getAssurantItemId() && $this->cartContains($item->getAssurantItemId())) {
                /*ignore when there is no change to discount amount*/
                if ($item->getData('discount_amount') == $item->getOrigData('discount_amount')) continue;

                $changed_amount = $item->getData('discount_amount') - $item->getOrigData('discount_amount');
                $changed_price = $item->getCalculationPrice() - ($changed_amount > 0 ? $changed_amount : 0);

                /*get Assurant products matched to updated price of this product*/
                $aproducts = Mage::helper('pro2/match')->getAPsbyMP($item->getProduct(), $changed_price);
                $ap = $aproducts->getFirstItem();
                if ($ap->getId()) {
                    $ap = Mage::getModel('catalog/product')->loadByAttribute('sku', 'assurant_'.$ap->getAssurantId());

                    /*remove current invalid assurant item in cart. add new item to cart*/
                    $quote->removeItem($item->getAssurantItemId());

                    /*add new valid product to cart*/
                    Mage::app()->getRequest()->setParam('assurant_stamp', $item->getBuyRequest()->getData('assurant_stamp'));
                    Mage::helper('pro2')->addAssurantProduct($item->getProduct(), $ap->getId());

                    Mage::getSingleton('checkout/session')->addNotice('Protection Plan for SKU '.$item->getSku().' has been updated because item price was changed.');
                }
                else {
                    $quote->removeItem($item->getAssurantItemId())->save();
                    Mage::getSingleton('checkout/session')->addNotice('There is no protection plan for SKU '.$item->getSku().' with Price '. Mage::helper('core')->currency($changed_price));
                }
            }
        }
    }

    /**
     * check for an item in cart
     *
     * @author atheotsky
     */
    public function cartContains($item_id)
    {
        foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item) {
            if ($item->getId() == $item_id) return true;
        }
        return false;
    }

    /**
     * change assurant product name so name in order will look nicer
     *
     * @author atheotsky
     */
    public function changeAssurantProductName($observer)
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $attribute_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);
        $product = Mage::getModel('catalog/product')->load($observer->getProduct()->getId());
        if ($product->getAttributeSetId() == $attribute_set_id) {
            $option_id = $observer->getQuoteItem()->getAssurantItemOptionId() ? $observer->getQuoteItem()->getAssurantItemOptionId() : $product->getDefaultOptionId();
            $plans = unserialize($product->getOptionsHash());
            foreach ($plans as $p) {
                if ($p->id == $option_id) {
                    $years = $p->period/12;
                    if (is_float($years)) $years = number_format($years, 1);
                    $years = $years ? "{$years}-Year" : '';
                    $observer->getQuoteItem()->setName("Assurant&reg; {$years} product protection plan");
                }
            }
        }
    }

    /**
     * update Assurant Product Matches if there is price change. for catalog_product_save_after event. only work with Magento product
     *
     * @author atheotsky
     */
    public function updateAssurantMatches($observer)
    {
        $session = Mage::getSingleton('core/session');
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $assurant_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);
        if ($session->getMatchInProgress() !== true) { // check lock before begin
            if ($observer->getProduct()->getAttributeSetId() != $assurant_set_id && $observer->getProduct()->getOrigData('price') != $observer->getProduct()->getData('price')) {
                Mage::helper('pro2/match')->matchMP($observer->getProduct());
            }
        }
    }
}
