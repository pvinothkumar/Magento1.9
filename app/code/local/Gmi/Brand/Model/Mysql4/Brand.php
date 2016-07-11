<?php
  
class Gmi_Brand_Model_Mysql4_Brand extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {  
        $this->_init('brand/brand', 'brand_id');
    }
}