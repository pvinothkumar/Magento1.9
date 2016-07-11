<?php

class Assurant_Pro2_Block_Adminhtml_Catalog_Product_Edit_Tab
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function canShowTab() 
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $attribute_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);
        return $attribute_set_id != Mage::registry('current_product')->getAttributeSetId();
    }

    public function getTabLabel() 
    {
        return $this->__('Assurant Products');
    }

    public function getTabTitle()        
    {
        return $this->__('Link Assurant Products to Current Product');
    }

    public function isHidden()
    {
        return false;
    }

    public function getTabUrl() 
    {
        return $this->getUrl('*/*/assurant', array('_current' => true));
    }

    public function getTabClass()
    {
        return 'ajax';
    }

}
