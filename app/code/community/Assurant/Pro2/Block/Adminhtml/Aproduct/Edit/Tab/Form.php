<?php
class Assurant_Pro2_Block_Adminhtml_Aproduct_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("pro2_form", array("legend"=>Mage::helper("pro2")->__("Assurant Product information")));


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

        $fieldset->addField("description", "text", array(
            "label" => Mage::helper("pro2")->__("Description"),
            "name" => "description",
            'readonly' => true,
        ));

        $fieldset->addField("last_synced_at", "text", array(
            "label" => Mage::helper("pro2")->__("Last Sync Date"),
            "name" => "last_synced_at",
            'readonly' => true,
        ));


        if (Mage::getSingleton("adminhtml/session")->getAproductData())
        {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getAproductData());
            Mage::getSingleton("adminhtml/session")->setAproductData(null);
        } 
        elseif(Mage::registry("aproduct_data")) {
            $form->setValues(Mage::registry("aproduct_data")->getData());
        }
        return parent::_prepareForm();
    }
}
