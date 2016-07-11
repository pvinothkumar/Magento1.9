<?php

require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Catalog'.DS.'ProductController.php');

class Assurant_Pro2_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    /**
     * Get custom products grid and serializer block
     */
    public function assurantAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.assurant')
            ->setProductsAssurant($this->getRequest()->getPost('products_assurant', null));
        $this->renderLayout();
    }

    /**
     * Get custom products grid
     */
    public function assurantGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.assurant')
            ->setProductsRelated($this->getRequest()->getPost('products_assurant', null));
        $this->renderLayout();
    }

}
