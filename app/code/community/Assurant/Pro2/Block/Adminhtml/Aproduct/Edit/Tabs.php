<?php
class Assurant_Pro2_Block_Adminhtml_Aproduct_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId("aproduct_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("pro2")->__("Assurant Product Information"));
    }

    protected function _beforeToHtml() {
        $this->addTab("form_section", array(
            "label" => Mage::helper("pro2")->__("General"),
            "title" => Mage::helper("pro2")->__("General Information cAssurant Product"),
            "content" => $this->getLayout()->createBlock("pro2/adminhtml_aproduct_edit_tab_form")->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
