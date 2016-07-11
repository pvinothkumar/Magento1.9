<?php
/**
 * Adminhtml ordered protection plans report gri block
 *
 * @deprecated after 1.4.0.1
 */
class Assurant_Pro2_Block_Adminhtml_Report_Assurant_Ordered_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridOrderedPlans');
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('pro2/report_ordered_collection');
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'index'     =>'order_item_name'
        ));

        $baseCurrencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('price', array(
            'header'        => Mage::helper('reports')->__('Price'),
            'width'         => '120px',
            'type'          => 'currency',
            'currency_code' => $baseCurrencyCode,
            'index'         => 'price',
            'rate'          => $this->getRate($baseCurrencyCode),
        ));

        $this->addColumn('ordered_qty', array(
            'header'    =>Mage::helper('reports')->__('Quantity Ordered'),
            'width'     =>'120px',
            'align'     =>'right',
            'index'     =>'ordered_qty',
            'total'     =>'sum',
            'type'      =>'number'
        ));

        //$this->addExportType('*/*/exportOrderedCsv', Mage::helper('reports')->__('CSV'));
        //$this->addExportType('*/*/exportOrderedExcel', Mage::helper('reports')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
