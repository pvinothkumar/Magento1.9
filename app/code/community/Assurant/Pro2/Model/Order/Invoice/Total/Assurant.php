<?php
class Assurant_Pro2_Model_Order_Invoice_Total_Assurant
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $order=$invoice->getOrder();
        $orderAssurantTotal = $order->getAssurantTotal();
        if ($orderAssurantTotal&&count($order->getInvoiceCollection())==0) {
            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderAssurantTotal);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$orderAssurantTotal);
        }
        return $this;
    }
}
