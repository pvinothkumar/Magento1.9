<?php
class Assurant_Pro2_Helper_Match extends Mage_Core_Helper_Abstract {

    /**
     * match a Magento product to Assurant products
     *
     * @author atheotsky
     */
    public function matchMP($mp)
    {
        $collection = $mp->getAssurantProductCollection();
        if ($collection && $collection->getFirstItem()) {
            $current_ap = Mage::getModel('catalog/product')->load($collection->getFirstItem()->getId());
            $require_match = !$current_ap->getId() || ($current_ap->getId() && $mp->getPrice() != $current_ap->getPrice());

            if ($require_match) {
                /*remove old matches*/
                $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
                $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_link');
                $binds = ['link_type_id' => Assurant_Pro2_Model_Catalog_Product_Link::LINK_TYPE_ASSURANT, 'product_id' => $mp->getId()];
                $adapter->query("delete from {$tableName} where link_type_id = :link_type_id and product_id = :product_id", $binds);

                /*add new matches*/
                $collection = $this->getAPsbyMP($mp, $mp->getPrice());
                foreach ($collection as $ap) {
                    Mage::getSingleton('core/session')->setByPassAssurantLock(true); // need to bypass assurant lock
                    $ap = Mage::getModel('catalog/product')->loadByAttribute('sku', 'assurant_'.$ap->getAssurantId());
                    if ($ap && $ap->getId()) {
                        $this->linkA2M($ap, $mp);
                    }
                }
            }
        }
    }

    /**
     * link assurant product to Magento Products. Need to get all categories linked to Assurant Product's category. get all products , check price then match
     *
     * @param $ap
     * @author atheotsky
     */
    public function matchAP($ap)
    {
        $ac = Mage::getModel('pro2/acategory')->getCollection()->addFieldToFilter('assurant_id', $ap->getAssurantCategoryId())->getFirstItem();
        $matches = array_unique(explode(',', $ac->getMatches()));
        $category_ids = array();
        foreach ($matches as $id) {
            if (intval($id) > 0) $category_ids[] = intval($id);
        }

        if (count($category_ids)) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left');
            $collection->addAttributeToFilter('category_id', array('in' => $category_ids));
            // apply price filter here
            $collection->addAttributeToFilter('price' , array('gteq' => $ap->getMatchMin()));
            $collection->addAttributeToFilter('price' , array('lteq' => $ap->getMatchMax()));

            if ($collection->getSize() > 0 ) {
                foreach ($collection as $mp) {
                    $this->linkA2M($ap, $mp);
                }
            }
        }
    }

    /**
     * link 1 Assurant Product to 1 magento Proudct. direct without price check
     *
     * @author atheotsky
     */
    private function linkA2M($ap, $mp) {
        /*check match override to see these 2 products can be matched or not*/
        if (Mage::getModel('pro2/amatchoverride')->linkAllowed($ap, $mp)) {
            $session = Mage::getSingleton('core/session'); // lock updateAssurantMatches from being invoked with product save here . avoid recursive call
            $session->setMatchInProgress(true);

            /*make sure we update it instead of replacing existing links*/
            $link_data = array($ap->getId() => array('position' => 0));
            foreach ($mp->getAssurantLinkCollection() as $link) {
                /*check match override before matching*/

                $link_data[$link->getLinkedProductId()] = array('position' => 0);
            }

            $mp->setDirectLink(true); // bypass maintain manual edit feature
            $mp->setAssurantLinkData($link_data);
            $mp->save();

            $session->unsMatchInProgress(); // unlock
        }
    }

    /**
     * get Assurant products  that should be linked to a Magento Product
     *
     * @author atheotsky
     */
    public function getAPsbyMP($mp, $price=false)
    {
        Mage::getSingleton('core/session')->setByPassAssurantLock(true); // need to bypass assurant lock
        $acategories = Mage::getModel('pro2/acategory')->getCollection();
        $conditions = array();
        foreach ($mp->getCategoryCollection() as $c) {
            $conditions[] = array(
                'like' => "%{$c->getId()}%"
            );
        }
        if (empty($conditions)) return array();
        
        $acategories->addFieldToFilter('matches', $conditions);

        $acategory_ids = array();
        foreach ($acategories as $ac) {
            $acategory_ids[] = $ac->getAssurantId();
        }
        $aproducts = Mage::getModel('pro2/aproduct')->getCollection();
        $aproducts->addFieldToSelect('*')->addFieldToFilter('assurant_category_id', array('in' => $acategory_ids));
        if ($price !== false) {
            $aproducts->addFieldToFilter('match_min', array('lteq' => $price));
            $aproducts->addFieldToFilter('match_max', array('gteq' => $price));
        }

        return $aproducts;
    }
}
