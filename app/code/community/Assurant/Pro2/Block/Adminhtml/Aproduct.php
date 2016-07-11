<?php
class Assurant_Pro2_Block_Adminhtml_Aproduct extends Mage_Adminhtml_Block_Widget_Grid_Container{

    public function __construct() {
        $this->_controller = "adminhtml_aproduct";
        $this->_blockGroup = "pro2";
        $this->_headerText = Mage::helper("pro2")->__("Assurant products Manager");
        parent::__construct();
        $this->_removeButton('add');
    }

}
