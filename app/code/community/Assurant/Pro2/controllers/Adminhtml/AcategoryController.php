<?php

class Assurant_Pro2_Adminhtml_AcategoryController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()->_setActiveMenu("pro2/acategory")->_addBreadcrumb(Mage::helper("adminhtml")->__("Assurant"),Mage::helper("adminhtml")->__("Categories Manager"));
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__("Assurant Product Protection"));
        $this->_title($this->__("Manage Assurant Categories"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction() {			    
        $this->_title($this->__("Pro2"));
        $this->_title($this->__("Assurant Category"));
        $this->_title($this->__("Edit Category"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("pro2/acategory")->load($id);
        if ($model->getId()) {
            Mage::register("acategory_data", $model);
            $this->loadLayout();
            $this->_setActiveMenu("pro2/acategory");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Assurant"), Mage::helper("adminhtml")->__("Assurant Category Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Category Description"), Mage::helper("adminhtml")->__("Assurant Category Description"));
            $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock("pro2/adminhtml_acategory_edit"))->_addLeft($this->getLayout()->createBlock("pro2/adminhtml_acategory_edit_tabs"));
            $this->renderLayout();
        } 
        else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("pro2")->__("Category does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function saveAction() {
        $post_data=$this->getRequest()->getPost();

        if ($post_data) {

            if ($post_data['category_ids']) {
                $matches = explode(',', $post_data['category_ids']);
                $post_data['matches'] = ','.implode(',', array_unique($matches)).',';
                unset($post_data['category_ids']);
            }
            try {
                $model = Mage::getModel("pro2/acategory")
                    ->addData($post_data)
                    ->setId($this->getRequest()->getParam("id"))
                    ->save();

                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Assurant Category was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setAcategoryData(false);

                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } 
            catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setAcategoryData($this->getRequest()->getPost());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }

        }
        $this->_redirect("*/*/");
    }

    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("pro2/acategory")->load($id);
        if ($model->getId()) Mage::register("acategory_data", $model);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('pro2/adminhtml_acategory_edit_tab_match')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() {
        $fileName   = 'assurant_categories.csv';
        $grid       = $this->getLayout()->createBlock('pro2/adminhtml_acategory_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    } 

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() {
        $fileName   = 'assurant_categories.xml';
        $grid       = $this->getLayout()->createBlock('pro2/adminhtml_acategory_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
