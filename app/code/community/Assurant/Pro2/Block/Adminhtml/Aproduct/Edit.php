<?php
class Assurant_Pro2_Block_Adminhtml_Aproduct_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct() {
        parent::__construct();
        $this->_objectId = "id";
        $this->_blockGroup = "pro2";
        $this->_controller = "adminhtml_aproduct";
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
        if( Mage::registry("aproduct_data") && Mage::registry("aproduct_data")->getId() ){
            return Mage::helper("pro2")->__("Edit Assurant Product #%s", $this->htmlEscape(Mage::registry("aproduct_data")->getId()));
        } 
        else{
            return Mage::helper("pro2")->__("Add Item");
        }
    }
}
