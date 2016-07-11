<?php

class Assurant_Pro2_Adminhtml_AproductController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()->_setActiveMenu("pro2/aproduct")->_addBreadcrumb(Mage::helper("adminhtml")->__("Assurant"),Mage::helper("adminhtml")->__("Products Manager"));
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__("Assurant Product Protection"));
        $this->_title($this->__("Manage Assurant Products"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction() {			    
        $this->_title($this->__("Pro2"));
        $this->_title($this->__("Assurant Product"));
        $this->_title($this->__("Edit Product"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("pro2/aproduct")->load($id);
        if ($model->getId()) {
            Mage::register("aproduct_data", $model);
            $this->loadLayout();
            $this->_setActiveMenu("pro2/aproduct");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Aproduct Manager"), Mage::helper("adminhtml")->__("Assurant Product Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Aproduct Description"), Mage::helper("adminhtml")->__("Assurant Product Description"));
            $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock("pro2/adminhtml_aproduct_edit"))->_addLeft($this->getLayout()->createBlock("pro2/adminhtml_aproduct_edit_tabs"));
            $this->renderLayout();
        } 
        else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("pro2")->__("Product does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function saveAction() {
        $post_data=$this->getRequest()->getPost();

        if ($post_data) {

            try {
                $model = Mage::getModel("pro2/aproduct")
                    ->addData($post_data)
                    ->setId($this->getRequest()->getParam("id"))
                    ->save();

                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Assurant Product was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setAproductData(false);

                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } 
            catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setAproductData($this->getRequest()->getPost());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }

        }
        $this->_redirect("*/*/");
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() {
        $fileName   = 'assurant_products.csv';
        $grid       = $this->getLayout()->createBlock('pro2/adminhtml_aproduct_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    } 

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() {
        $fileName   = 'assurant_products.xml';
        $grid       = $this->getLayout()->createBlock('pro2/adminhtml_aproduct_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
