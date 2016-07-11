<?php

class Assurant_Pro2_Model_Catalog_Product extends Mage_Catalog_Model_Product
{
    /**
     * Retrieve array of assurant products
     *
     * @return array
     */
    public function getAssurantProducts()
    {
        if (!$this->hasAssurantProducts()) {
            $products = array();
            $collection = $this->getAssurantProductCollection();
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setAssurantProducts($products);
        }
        return $this->getData('assurant_products');
    }

    /**
     * Retrieve assurant products identifiers
     *
     * @return array
     */
    public function getAssurantProductIds()
    {
        if (!$this->hasAssurantProductIds()) {
            $ids = array();
            foreach ($this->getAssurantProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setAssurantProductIds($ids);
        }
        return $this->getData('assurant_product_ids');
    }

    /**
     * Retrieve collection assurant product
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getAssurantProductCollection()
    {
        Mage::getSingleton('core/session')->setByPassAssurantLock(true);
        $collection = $this->getLinkInstance()->useAssurantLinks()
            ->getProductCollection()
            ->setIsStrongMode();
        $collection->setProduct($this);

        if ($collection && $collection->getSize()) {
            return $collection;
        }
        else {
            /* return empty collection */
            $collection = new Mage_Catalog_Model_Resource_Product_Link_Product_Collection();
            $collection->getSelect()->where('entity_id IS NULL');
            return $collection;
        }
    }

    /**
     * Retrieve collection assurant link
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Collection
     */
    public function getAssurantLinkCollection()
    {
        Mage::getSingleton('core/session')->setByPassAssurantLock(true);
        $collection = $this->getLinkInstance()->useAssurantLinks()
            ->getLinkCollection();
        $collection->setProduct($this);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }

}
