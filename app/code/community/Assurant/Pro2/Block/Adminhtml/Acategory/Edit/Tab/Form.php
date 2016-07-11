<?php
class Assurant_Pro2_Block_Adminhtml_Acategory_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("pro2_form", array("legend"=>Mage::helper("pro2")->__("Assurant Category information")));


        $fieldset->addField("id", "text", array(
            "label" => Mage::helper("pro2")->__("ID"),
            "name" => "id",
            'readonly' => true,
        ));

        $fieldset->addField("name", "text", array(
            "label" => Mage::helper("pro2")->__("Name"),
            "name" => "name",
            'readonly' => true,
        ));

        $fieldset->addField("type", "text", array(
            "label" => Mage::helper("pro2")->__("Type"),
            "name" => "type",
            'readonly' => true,
        ));

        $fieldset->addField("updated_at", "text", array(
            "label" => Mage::helper("pro2")->__("Last Sync Date"),
            "name" => "updated_at",
            'readonly' => true,
        ));


        if (Mage::getSingleton("adminhtml/session")->getAcategoryData())
        {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getAcategoryData());
            Mage::getSingleton("adminhtml/session")->setAcategoryData(null);
        } 
        elseif(Mage::registry("acategory_data")) {
            $form->setValues(Mage::registry("acategory_data")->getData());
        }
        return parent::_prepareForm();
    }
}
