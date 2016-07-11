<?php
class Assurant_Pro2_Block_Product_List_Aproduct extends Mage_Catalog_Block_Product_Abstract
{
	protected $_items;
    protected $_itemCollection;

    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    protected function _prepareData()
    {

        $this->_itemCollection = $this->getProduct()->getAssurantProductCollection()
            ->setPositionOrder()
            ->addAttributeToSelect('*');

        $this->_itemCollection->load();

        return $this->_itemCollection; 
    }

    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    public function getItemCollection()
    {
        return $this->_itemCollection;
    }

    public function getItems()
    {
        if (is_null($this->_items) && $this->getItemCollection()) {
            $this->_items = $this->getItemCollection()->getItems();
        }
        return $this->_items;
    }

    /**
     * get All Plans of a Assurant Product
     *
     * @author atheotsky
     */
    public function getPlans($product)
    {
        $options_hash = $product->getOptionsHash();
        $options = unserialize($options_hash);
        usort($options, array('self', 'sortByPeriod'));
        return $options;
    }

    private static function sortByPeriod($a, $b)
    {
        return $a->period - $b->period;
    }

    /**
     * read all options of a plan, return html string
     *
     * @author atheotsky
     */
    public function parsePlanOptions($plan)
    {
        $supported_attributes = array(
            'period',
            'theft',
            'water',
            'robbery',
            'burglary',
            'damage_protection',
            'warranty_extension',
            'travel_cancellation',
            'travel_interruption',
        );

        $options = array();
        foreach ($supported_attributes as $attribute) {
            if (property_exists($plan, $attribute) && Mage::getStoreConfig('aintegration/setting/assurant_content_plan_' . $attribute)) {
                $attribute_value = $plan->{$attribute};
                if ($attribute_value === true) {
                    $attribute_value = 'yes';
                }
                elseif ($attribute_value === false) {
                    $attribute_value = 'no';
                }

                $options[] = "<li>"
                    .Mage::getStoreConfig('aintegration/setting/assurant_content_plan_' . $attribute)
                    ." : {$attribute_value}</li>";
            }
        }

        $options = implode('', $options);
        return "<ul>{$options}</ul>";
    }

    /**
     * get Assurant Plan name by period
     */
    public function getPlanName($p)
    {
        if ($p) {
            $years = $p->period/12;
            if (is_float($years)) $years = number_format($years, 1);
            $years = $years ? "{$years}-Year" : '';
        }

        return " - Assurant&reg; {$years} product protection plan";
    }

}
