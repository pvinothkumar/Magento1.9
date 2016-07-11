<?php
  
class Gmi_Brand_Block_Adminhtml_Brand_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('brandGrid');
        $this->setDefaultSort('brand_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
  
    protected function _prepareCollection()
    {
    	$newValu = false;
        $collection = Mage::getModel('brand/brand')->getCollection();
		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product','brand');
		$brand = array();
		foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
			$brand[$option['value']] = $attributeArray[$option['value']] = $option['label'];
		}
		$brand = array_filter($brand);
		$brand1count = count($brand);		
		if($brand1count!=$collection->getSize() && $brand1count>$collection->getSize()){
			$collection->addFieldToSelect('title');
			$newBrand = $collection->getData();
			$grBrMat = array();
			foreach($newBrand as $brandS){
				$grBrMat[] = $brandS['title']; 			
			}
			$insBrand = array_diff($brand,$grBrMat);
			foreach ($insBrand as $key => $singBrand){
				$newValu = true;
				$brandModel = Mage::getModel('brand/brand');
				$time_created = date("Y/m/d.h:i:sa");
				$brandModel	-> setTitle($singBrand) 
							-> setBrandAttrId($key)		
							-> setCreatedTime($time_created)		
							-> save();	
			}
									
		} 
		if($newValu) 
		$collection = Mage::getModel('brand/brand')->getCollection();
		$this->setCollection($collection);	
		return parent::_prepareCollection();
	}
  
    protected function _prepareColumns()
    {
        $this->addColumn('brand_id', array(
            'header'    => Mage::helper('brand')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'brand_id',
        ));
		
		$this->addColumn('title', array(
            'header'    => Mage::helper('brand')->__('Brand Name'),
            'align'     =>'left',
            'index'     => 'title',
        ));
		
        $this->addColumn('position', array(
            'header'    => Mage::helper('brand')->__('Position'),
            'align'     =>'left',
            'index'     => 'position',
        ));
		
		$this->addColumn('shown_frontend', array(
            'header'    => Mage::helper('brand')->__('Is Enabled On Frontend ?'),
            'align'     =>'left',
            'index'     => 'shown_frontend',
            'type'      => 'options',
            'options'   => array(
                1 => 'Yes',
                0 => 'No',
            ),
        ));
  
        $this->addColumn('created_time', array(
            'header'    => Mage::helper('brand')->__('Creation Time'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'created_time',
        ));
  
        $this->addColumn('update_time', array(
            'header'    => Mage::helper('brand')->__('Update Time'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'update_time',
        ));  
  
        $this->addColumn('status', array(
            'header'    => Mage::helper('brand')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));
		
        return parent::_prepareColumns();
    }
  
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
  
    public function getGridUrl()
    {
      return $this->getUrl('*/*/grid', array('_current'=>true));
    }
  
  
} 
