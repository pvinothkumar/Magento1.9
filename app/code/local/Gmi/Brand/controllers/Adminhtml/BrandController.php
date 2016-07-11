<?php

class Gmi_Brand_Adminhtml_BrandController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this -> loadLayout() -> _setActiveMenu('brand/items') -> _addBreadcrumb(Mage::helper('adminhtml') -> __('Items Manager'), Mage::helper('adminhtml') -> __('Item Manager'));
		return $this;
	}

	public function indexAction() {
		$this -> _initAction();	
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('brand/adminhtml_brand'));
        $this->renderLayout();
	}

	public function editAction() {
		$brandId = $this -> getRequest() -> getParam('id');
		$brandModel = Mage::getModel('brand/brand') -> load($brandId);

		if ($brandModel -> getId() || $brandId == 0) {
			Mage::register('brand_data', $brandModel);
			$this -> loadLayout();
			$this -> _setActiveMenu('brand/items');
			$this -> _addBreadcrumb(Mage::helper('adminhtml') -> __('Item Manager'), Mage::helper('adminhtml') -> __('Item Manager'));
			$this -> _addBreadcrumb(Mage::helper('adminhtml') -> __('Item News'), Mage::helper('adminhtml') -> __('Item News'));
			$this -> getLayout() -> getBlock('head') -> setCanLoadExtJs(true);
			$this -> _addContent($this -> getLayout() -> createBlock('brand/adminhtml_brand_edit')) -> _addLeft($this -> getLayout() -> createBlock('brand/adminhtml_brand_edit_tabs'));
			$this -> renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session') -> addError(Mage::helper('brand') -> __('Item does not exist'));
			$this -> _redirect('*/*/');
		}
	}

	public function newAction() {
		$this -> _forward('edit');
	}

	public function saveAction() {
		if ($this -> getRequest() -> getPost()) {
			try {
				$time_updated = date("Y/m/d.h:i:sa");
				$postData = $this -> getRequest() -> getPost();
				$brandModel = Mage::getModel('brand/brand');
				if (isset($_FILES) || isset($_FILES['filename']['name'])) {
					try {
						$uploader = new Varien_File_Uploader('filename');
						$uploader -> setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
						$uploader -> setAllowRenameFiles(true);
						$uploader -> setFilesDispersion(false);
						$path = Mage::getBaseDir('media').DS.'brand';
						$uploader -> save($path, $_FILES['filename']['name']);
						$data['filename'] = $uploader -> getUploadedFileName();
					} catch (Exception $e) {
						Mage::getSingleton('adminhtml/session') -> addError($e -> getMessage());				
					}
				}
				
				$delete = false;
				if (isset($postData['filename']['delete']) && $postData['filename']['delete'] == 1) {
					$new = Mage::getModel('brand/brand') -> load($this -> getRequest() -> getParam('id'));
					unlink('media/brand/' . $new -> getFilename());
					$delete = true;
				}
				
				$brandModel -> setId($this -> getRequest() -> getParam('id'));
				if (isset($data['filename'])) {
					$brandModel -> setFilename($data['filename']);
				}
				if ($delete) {
					$brandModel -> setFilename('');
				}
				
				$brandModel	-> setTitle($postData['title']) 
							-> setStatus($postData['status']) 
							-> setPosition($postData['position']) 
							-> setShownFrontend($postData['shown_frontend']) 
							-> setUpdateTime($time_updated)							
							-> save();
							
				Mage::getSingleton('adminhtml/session') -> addSuccess(Mage::helper('adminhtml') -> __('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session') -> setrandData(false);

				if ($this->getRequest()->getParam('back')) {
				    $this->_redirect(
				        '*/*/edit',
				        array(
				            'id' => $brandModel->getId()
				        )
				    );
				    return;
				}
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session') -> addError($e -> getMessage());
				Mage::getSingleton('adminhtml/session') -> setBrandData($this -> getRequest() -> getPost());
				$this -> _redirect('*/*/edit', array('id' => $this -> getRequest() -> getParam('id')));
				return;
			}
		}
		$this -> _redirect('*/*/');
	}

	public function deleteAction() {
		if ($this -> getRequest() -> getParam('id') > 0) {
			try {
				$brandModel = Mage::getModel('brand/brand');

				$brandModel -> setId($this -> getRequest() -> getParam('id')) -> delete();

				Mage::getSingleton('adminhtml/session') -> addSuccess(Mage::helper('adminhtml') -> __('Item was successfully deleted'));
				$this -> _redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session') -> addError($e -> getMessage());
				$this -> _redirect('*/*/edit', array('id' => $this -> getRequest() -> getParam('id')));
			}
		}
		$this -> _redirect('*/*/');
	}

	public function gridAction() {
		$this -> loadLayout();
		$this -> getResponse() -> setBody($this -> getLayout() -> createBlock('brand/adminhtml_brand_grid') -> toHtml());
	}

}
