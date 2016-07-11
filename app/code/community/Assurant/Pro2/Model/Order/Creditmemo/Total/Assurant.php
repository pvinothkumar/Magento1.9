<?php
class Assurant_Pro2_Model_Order_Creditmemo_Total_Assurant 
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        return $this;

        $order = $creditmemo->getOrder();
        $orderAssurantTotal        = $order->getAssurantTotal();

        if ($orderAssurantTotal) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$orderAssurantTotal);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$orderAssurantTotal);
        }

        return $this;
    }
}
