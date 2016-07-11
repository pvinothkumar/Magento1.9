<?php
class Assurant_Pro2_Model_Quote_Address_Total_Assurant 
    extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function __construct() {
        $this -> setCode('assurant_total');
    }
    /**
     * Collect totals information about assurant
     * 
     * @param Mage_Sales_Model_Quote_Address $address 
     * @return Mage_Sales_Model_Quote_Address_Total_Shipping 
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent :: collect($address);
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
        $quote= $address->getQuote();

        //amount definition

        $assurantAmount = 0.01;

        //amount definition

        $assurantAmount = $quote->getStore()->roundPrice($assurantAmount);
        $this->_setAmount($assurantAmount)->_setBaseAmount($assurantAmount);
        $address->setData('assurant_total',$assurantAmount);

        return $this;
    }

    /**
     * Add assurant totals information to address object
     * 
     * @param Mage_Sales_Model_Quote_Address $address 
     * @return Mage_Sales_Model_Quote_Address_Total_Shipping 
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        parent :: fetch($address);
        $amount = $address->getTotalAmount($this->getCode());
        if ($amount != 0){
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $amount
            ));
        }

        return $this;
    }

    /**
     * Get label
     * 
     * @return string 
     */
    public function getLabel()
    {
        return Mage :: helper('pro2') -> __('Assurant total');
    }
}
