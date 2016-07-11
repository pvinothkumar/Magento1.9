<?php
/**
 * Adminhtml ordered protection plans report content block
 *
 * @author atheotsky
 */
class Assurant_Pro2_Block_Adminhtml_Report_Assurant_Ordered extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'pro2';
        $this->_controller = 'adminhtml_report_assurant_ordered';
        $this->_headerText = Mage::helper('pro2')->__('Protection Plans Ordered');
        parent::__construct();
        $this->_removeButton('add');
    }
}
