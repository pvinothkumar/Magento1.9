<?php
class Assurant_Pro2_Block_Adminhtml_Acategory_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct() {
        parent::__construct();
        $this->_objectId = "id";
        $this->_blockGroup = "pro2";
        $this->_controller = "adminhtml_acategory";
        $this->_updateButton("save", "label", Mage::helper("pro2")->__("Save Item"));

        $this->_addButton("saveandcontinue", array(
            "label"     => Mage::helper("pro2")->__("Save And Continue Edit"),
            "onclick"   => "saveAndContinueEdit()",
            "class"     => "save",
        ), -100);



        $this->_formScripts[] = "
                function saveAndContinueEdit(){
                    editForm.submit($('edit_form').action+'back/edit/');
                }";

        $this->removeButton('delete');
    }

    public function getHeaderText() {
        if( Mage::registry("acategory_data") && Mage::registry("acategory_data")->getId() ){
            return Mage::helper("pro2")->__("Edit Assurant Category #%s", $this->htmlEscape(Mage::registry("acategory_data")->getId()));
        } 
        else{
            return Mage::helper("pro2")->__("Add Item");
        }
    }
}
