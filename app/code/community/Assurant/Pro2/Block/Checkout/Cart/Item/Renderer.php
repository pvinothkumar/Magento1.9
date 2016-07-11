<?php
class Assurant_Pro2_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer {
    /**
     * Get item product name . build Assurant product name
     *
     * @return string
     */
    public function getProductName()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $attribute_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);
        if ($this->getProduct()->getAttributeSetId() == $attribute_set_id && $this->getItem()->getAssurantItemOptionId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProduct()->getId());
            $options = unserialize($product->getOptionsHash());
            foreach ($options as $plan) {
                if ($plan->id != $this->getItem()->getAssurantItemOptionId()) continue;

                $years = $plan->period/12;
                if (is_float($years)) $years = number_format($years, 1);
                $years = $years ? "{$years}-Year" : '';
                return "Assurant&reg; {$years} product protection plan";
            }
        }

        return parent::getProductName();
    }
}
