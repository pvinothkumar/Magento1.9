<?php

class Assurant_Pro2_Block_Adminhtml_Aproduct_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId("aproductGrid");
        $this->setDefaultSort("id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel("pro2/aproduct")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn("id", array(
            "header" => Mage::helper("pro2")->__("ID"),
            "align" =>"right",
            "width" => "50px",
            "type" => "number",
            "index" => "id",
        ));

        $this->addColumn("name", array(
            "header" => Mage::helper("pro2")->__("Name"),
            "index" => "name",
        ));
        $this->addColumn("description", array(
            "header" => Mage::helper("pro2")->__("Description"),
            "index" => "description",
        ));
        $this->addColumn("last_synced_at", array(
            "header" => Mage::helper("pro2")->__("Last Sync Date"),
            "index" => "last_synced_at",
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        //$this->getMassactionBlock()->addItem('remove_aproduct', array(
            //'label'=> Mage::helper('pro2')->__('Remove Aproduct'),
            //'url'  => $this->getUrl('*/adminhtml_aproduct/massRemove'),
            //'confirm' => Mage::helper('pro2')->__('Are you sure?')
        //));
        return $this;
    }
}
