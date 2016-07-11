<?php

class Gmi_Brand_Block_Adminhtml_Brand_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	public function __construct() {
		parent::__construct();

		$this -> _objectId = 'id';
		$this -> _blockGroup = 'brand';
		$this -> _controller = 'adminhtml_brand';

		$this -> _updateButton('save', 'label', Mage::helper('brand') -> __('Save Item'));
		//$this->_updateButton('delete', 'label', Mage::helper('brand')->__('Delete Item'));
		$this -> _removeButton('delete');
		$this -> _addButton('saveandcontinue', array('label' => Mage::helper('adminhtml') -> __('Save And Continue Edit'), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save'), -100);

		$this -> _formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
	}

	public function getHeaderText() {
		if (Mage::registry('brand_data') && Mage::registry('brand_data') -> getId()) {
			return Mage::helper('brand') -> __("Edit Item '%s'", $this -> htmlEscape(Mage::registry('brand_data') -> getTitle()));
		} else {
			return Mage::helper('brand') -> __('Add Item');
		}
	}

}
