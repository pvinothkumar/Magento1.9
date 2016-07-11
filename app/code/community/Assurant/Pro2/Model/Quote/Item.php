<?php
/**
 * Patch to enabel multiple line item for a same product that has protection plans
 *
 * @author atheotsky
 */
class Assurant_Pro2_Model_Quote_Item extends Mage_Sales_Model_Quote_Item {

    /**
     * Check product representation in item
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if (!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }

        // check for product with protection plans. identified by assurant_stamp
        // in order to compare, assurant_stamp must be available
        if (Mage::getStoreConfig('aintegration/setting/enabled')
            && $this->getBuyRequest()->getData('assurant_stamp')
            && $product->getAssurantProductCollection()->count()) {
            $product_stamp = Mage::app()->getRequest()->getParam('assurant_stamp');
            $item_stamp = $this->getBuyRequest()->getData('assurant_stamp');
            if ($product_stamp || $item_stamp) {
                return $product_stamp == $item_stamp;
            }
            else {
                return false;
            }
        }

        /**
         * Check maybe product is planned to be a child of some quote item - in this case we limit search
         * only within same parent item
         */
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }

        // Check options
        $itemOptions = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
        return true;
    }
}
