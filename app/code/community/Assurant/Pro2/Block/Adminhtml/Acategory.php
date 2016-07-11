<?php
class Assurant_Pro2_Block_Adminhtml_Acategory extends Mage_Adminhtml_Block_Widget_Grid_Container{

    public function __construct() {
        $this->_controller = "adminhtml_acategory";
        $this->_blockGroup = "pro2";
        $this->_headerText = Mage::helper("pro2")->__("Assurant Categories Manager");
        parent::__construct();
        $this->_removeButton('add');
    }

}
