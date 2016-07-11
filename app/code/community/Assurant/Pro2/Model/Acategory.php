<?php
class Assurant_Pro2_Model_Acategory extends Mage_Core_Model_Abstract {
    protected function _construct(){
        $this->_init("pro2/acategory");
    }

    /**
     * return Matched Categories
     *
     * @author atheotsky
     */
    public function getCategoryIds()
    {
        $category_ids = array();
        foreach (explode(',', $this->getMatches()) as $id) {
            if (intval($id)) {
                $category_ids[] = intval($id);
            }
        }

        return $category_ids;
    }

    /**
     * handle after _beforeSave
     *
     * @author atheotsky
     */
    protected function _beforeSave() {
        /* check for matches changes, update existing matches */
        if ($this->getData('matches') != $this->getOrigData('matches')) {
            $newMatches = explode(',', $this->getData('matches'));
            $oldMathces = explode(',', $this->getOrigData('matches'));
            $removeMatches = array_diff($oldMathces, $newMatches);
            $overrideModel = Mage::getModel('pro2/amatchoverride');

            foreach ($removeMatches as $cid) {
                $products = Mage::getModel('catalog/category')->load($cid)->getProductCollection();
                $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_link');
                $products->getSelect()->join(array('link' => $tableName), 'e.entity_id = link.product_id', array())->group('entity_id');
                foreach ($products as $p) {
                    $p = Mage::getModel('catalog/product')->load($p->getEntityId());
                    foreach ($p->getAssurantProductCollection() as $ap) {
                        if ($this->getAssurantId() == Mage::getModel('catalog/product')->load($ap->getId())->getAssurantCategoryId()) {
                            $this->removeLink($p->getId(), $ap->getId());
                        }
                    }
                    $overrideModel->flushOverrideDeny($p->getId(), $this->getId());
                }
            }
        }

        return parent::_beforeSave();
    }

    private function removeLink($pid, $linkid) {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_link');
        $binds = ['product_id' => $pid, 'linked_product_id' => $linkid];
        $adapter->query("delete from {$tableName} where product_id = :product_id and linked_product_id = :linked_product_id", $binds);
    }

    /**
     * handle after _beforeDelete
     *
     * @author atheotsky
     */
    protected function _beforeDelete() {
        /* remove assurant products records . _beforeDelete from Aproduct will do the rest */
        $collection = Mage::getModel('pro2/aproduct')->getCollection()->addFieldToFilter('assurant_category_id', array('eq' => $this->getAssurantId()));
        foreach ($collection as $p) $p->delete();

        return parent::_beforeDelete();
    }
}
