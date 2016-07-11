<?php
class Assurant_Pro2_Model_Aproduct extends Mage_Core_Model_Abstract {

    const SET_NAME = 'assurant';
    const TYPE_ID = 'simple';

    protected function _construct(){
        $this->_init("pro2/aproduct");
    }

    /**
     * handle after _beforeSave
     *
     * @author atheotsky
     */
    protected function _beforeSave() {
        if ($this->getName() && $this->getAssurantId()) {
            $ap = $this->createAssurantProduct();
            $this->setAssurantProductId($ap->getId());
        }

        return parent::_beforeSave();
    }

    /**
     * handle after _beforeDelete
     *
     * @author atheotsky
     */
    protected function _beforeDelete() {
        /* remove assurant prouducts belong to deleted product */
        Mage::getSingleton('core/session')->setByPassAssurantLock(true);
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $sku = 'assurant_'.$this->getAssurantId();
        $product = Mage::getModel('catalog/product');
        $product_id = $product->getIdBySku($sku);
        $product->load($product_id);
        if ($product) $product->delete();

        /*unlink if there is any related products*/
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_link');
        $binds = ['link_type_id' => Assurant_Pro2_Model_Catalog_Product_Link::LINK_TYPE_ASSURANT, 'linked_product_id' => $product_id];
        $adapter->query("delete from {$tableName} where link_type_id = :link_type_id and linked_product_id = :linked_product_id", $binds);

        return parent::_beforeDelete();
    }
    
    /**
     * create new Magento proxy product for assurant productprogrammatically using Assurant attribute set
     *
     * @author atheotsky
     */
    private function createAssurantProduct() {
        $product = Mage::getModel('catalog/product');
        $sku = 'assurant_'.$this->getAssurantId();
        $product_id = $product->getIdBySku($sku);
        $product->load($product_id);
        $product->unsStockItem(); // make sure we always have new stock item for each new product. is it a Magento bug ?

        if (!$product->getId()) {
            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
            $attribute_set_id = $setup->getAttributeSetId('catalog_product', self::SET_NAME);

            $website = array();
            foreach (Mage::app()->getWebsites() as $values) {
                $website[] = $values->getWebsiteId();
            }

            $product->setAttributeSetId($attribute_set_id)
                ->setWebsiteIDs($website)
                ->setTypeId('simple')
                ->setSku('assurant_'.$this->getAssurantId())
                ->setName($this->getName())
                ->setWeight(0)
                ->setStatus(1)
                ->setTaxClassId(0)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
                ->setPrice($this->getPrice())
                ->setDescription($this->getDescription())
                ->setShortDescription($this->getDescription());
        }

        try{
            $product->setStockData(array(
                    'stock_id' => 1,
                    'use_config_manage_stock' => 0,
                    'manage_stock'=> 1,
                    'use_config_min_sale_qty'=> 1,
                    'use_config_max_sale_qty'=> 1,
                    'is_in_stock' => 1,
                    'qty' => 9999
                ))
                ->setAvbUrl($this->getAvbUrl())
                ->setPibUrl($this->getPibUrl())
                ->setMatchMin($this->getMatchMin())
                ->setMatchMax($this->getMatchMax())
                ->setMaxPeriod($this->getMaxPeriod())
                ->setDefaultOptionId($this->getDefaultOptionId())
                ->setInsuranceCompany($this->getInsuranceCompany())
                ->setOptionsHash($this->getOptionsHash())
                ->setAssurantCategoryId($this->getAssurantCategoryId());

            $product->save();
        }
        catch (Exception $e) {
            Mage::logException($e);
        }

        return $product;
    }
}
