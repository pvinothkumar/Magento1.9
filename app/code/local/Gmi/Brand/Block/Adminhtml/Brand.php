<?php
class Gmi_Brand_Block_Adminhtml_Brand extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_brand';
        $this->_blockGroup = 'brand';
        $this->_headerText = Mage::helper('brand')->__('Item Manager');
        parent::__construct();
		$this->_removeButton('add');
    }
}