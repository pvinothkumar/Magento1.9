<?php
  
class Gmi_Brand_Block_Adminhtml_Brand_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
   
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('brand_form', array('legend'=>Mage::helper('brand')->__('Item information')));
        
        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('brand')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
  
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('brand')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('brand')->__('Active'),
                ),
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('brand')->__('Inactive'),
                ),
            ),
        ));
	
        $fieldset->addField('filename', 'image', array(
            'name'      => 'filename',
            'label'     => Mage::helper('brand')->__('Image'),
            'required'  => true,
        ));

        $fieldset->addField('shown_frontend', 'select', array(
            'label'     => Mage::helper('brand')->__('Visible On Frontend ?'),
            'name'      => 'shown_frontend',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('brand')->__('Yes'),
                ),
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('brand')->__('No'),
                ),
            ),
        ));
        
		$fieldset->addField('position', 'text', array(
            'label'     => Mage::helper('brand')->__('Position'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'position',
        ));
		
        if ( Mage::getSingleton('adminhtml/session')->getbrandData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getbrandData());
            Mage::getSingleton('adminhtml/session')->setbrandData(null);
        } elseif ( Mage::registry('brand_data') ) {
            $form->setValues(Mage::registry('brand_data')->getData());
        }
        return parent::_prepareForm();
    }
} 