<?php
/**
 * Protection Plans Ordered Report collection
 *
 * @category    Assurant
 * @package     Pro2
 * @author      atheotsky
 */
class Assurant_Pro2_Model_Mysql4_Report_Ordered_Collection extends Mage_Reports_Model_Resource_Product_Collection
{
    /**
     * Join fields
     *
     * @param int $from
     * @param int $to
     * @return Mage_Reports_Model_Resource_Product_Ordered_Collection
     */
    protected function _joinFields($from = '', $to = '')
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $attribute_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);
        Mage::getSingleton('core/session')->setByPassAssurantLock(true);

        $this->addAttributeToSelect('*')
            ->addOrderedQty($from, $to)
            ->setOrder('ordered_qty', self::SORT_ORDER_DESC);

        $nameselect = clone $this->getSelect();
        $nameselect->reset();
        $nameselect->from(array('assurant' => 'sales_flat_order_item'), array());
        $nameselect->join(array('magento' => 'sales_flat_order_item'), 'assurant.quote_item_id = magento.assurant_item_id', array());
        $nameselect->columns(array('assurant.quote_item_id', 'assurant.name', 'assurant.price as a_price', 'magento.name as m_name'));
        $nameselect->where('assurant.assurant_item_option_id IS NOT NULL');
        
        $this->getSelect()->joinleft(array('iden' => $nameselect), 'order_items.quote_item_id = iden.quote_item_id');
        $this->getSelect()->columns(array('order_item_name' => 'CONCAT(order_items.name, " for ", iden.m_name)', 'a_price' => 'iden.a_price'));
        $this->getSelect()->where('attribute_set_id = ?', $attribute_set_id);

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param int $from
     * @param int $to
     * @return Mage_Reports_Model_Resource_Product_Ordered_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->_joinFields($from, $to);
        return $this;
    }

    /**
     * Set store ids
     *
     * @param array s$storeIds
     * @return Mage_Reports_Model_Resource_Product_Ordered_Collection
     */
    public function setStoreIds($storeIds)
    {
        $storeId = array_pop($storeIds);
        $this->setStoreId($storeId);
        $this->addStoreFilter($storeId);
        return $this;
    }
}
