<?php

class Assurant_Pro2_Block_Adminhtml_Acategory_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId("acategoryGrid");
        $this->setDefaultSort("id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel("pro2/acategory")->getCollection();
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
        $this->addColumn("type", array(
            "header" => Mage::helper("pro2")->__("type"),
            "index" => "type",
        ));
        $this->addColumn("last_synced_at", array(
            "header" => Mage::helper("pro2")->__("Last Sync Date"),
            "index" => "updated_at",
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
        return $this;
    }
}
