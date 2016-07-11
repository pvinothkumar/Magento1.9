<?php
class Assurant_Pro2_Adminhtml_Report_AssurantController extends Mage_Adminhtml_Controller_Report_Abstract
{
    /**
     * Add report/products breadcrumbs
     *
     * @return Mage_Adminhtml_Report_ProductController
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(Mage::helper('pro2')->__('Assurant'), Mage::helper('pro2')->__('Report'));
        return $this;
    }

    /**
     * Ordered Protection Plans Report Action
     *
     * @author atheotsky
     */
    public function orderedAction()
    {
        $this->_title($this->__('Assurant'))
             ->_title($this->__('Protection Plans Ordered'));

        $this->_initAction()
            ->_setActiveMenu('pro2/report')
            ->_addBreadcrumb(Mage::helper('pro2')->__('Assurant'), Mage::helper('pro2')->__('Report'))
            ->_addContent($this->getLayout()->createBlock('pro2/adminhtml_report_assurant_ordered'))
            ->renderLayout();
    }

    /**
     * Export products bestsellers report to CSV format
     *
     * @deprecated after 1.4.0.1
     */
    public function exportOrderedCsvAction()
    {
        $fileName   = 'assurant_plans_ordered.csv';
        $grid       = $this->getLayout()->createBlock('pro2/adminhtml_report_assurant_ordered_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export products bestsellers report to XML format
     *
     * @deprecated after 1.4.0.1
     */
    public function exportOrderedExcelAction()
    {
        $fileName   = 'assurant_plans_ordered.xml';
        $grid       = $this->getLayout()->createBlock('pro2/adminhtml_report_assurant_ordered_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
