<?php
class Assurant_Pro2_Block_Adminhtml_Acategory_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId("acategory_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("pro2")->__("Category Information"));
    }

    protected function _beforeToHtml() {
        $this->addTab("form_section", array(
            "label" => Mage::helper("pro2")->__("General"),
            "title" => Mage::helper("pro2")->__("General Category Information"),
            "content" => $this->getLayout()->createBlock("pro2/adminhtml_acategory_edit_tab_form")->toHtml(),
        ));
        $this->addTab("matches_section", array(
            "label" => Mage::helper("pro2")->__("Match"),
            "title" => Mage::helper("pro2")->__("Match Magento Categories"),
            "content" => $this->getLayout()->createBlock("pro2/adminhtml_acategory_edit_tab_match")->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
