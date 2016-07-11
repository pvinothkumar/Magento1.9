<?php

class Assurant_Pro2_Model_Catalog_Product_Link extends Mage_Catalog_Model_Product_Link
{
    const LINK_TYPE_ASSURANT   = 99;

    /**
     * @return Mage_Catalog_Model_Product_Link
     */
    public function useAssurantLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_ASSURANT);
        return $this;
    }

    /**
     * Save data for product relations
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Catalog_Model_Product_Link
     */
    public function saveProductRelations($product)
    {
        parent::saveProductRelations($product);

        $data = $product->getAssurantLinkData();
        if (!is_null($data)) {
            $this->_getResource()->saveProductLinks($product, $data, self::LINK_TYPE_ASSURANT);

            if (!$product->getDirectLink()) { // ignore direct link made by wizard or cron. only work with product save
                /*maintain manual edit made by merchant*/
                $matches = array(); $remove_matches = array();
                $model = Mage::getModel('catalog/product');
                $products = Mage::helper('pro2/match')->getAPsbyMP($product, $product->getPrice());
                foreach ($products  as $p) {
                    $sku = 'assurant_'.$p->getAssurantId();
                    $matches[] = $model->getIdBySku($sku);
                }
                foreach ($matches as $pid) {
                    if (empty($data[$pid])) {
                        $remove_matches[] = $pid;
                    }
                }
                Mage::getModel('pro2/amatchoverride')->updateOverride($product->getId(), $matches, $remove_matches);
            }
        }

    }
}
