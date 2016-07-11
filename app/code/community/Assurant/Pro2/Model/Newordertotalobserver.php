<?php
class Assurant_Pro2_Model_Newordertotalobserver {

    public function saveAssurantTotal(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        if($shippingAddress && $shippingAddress->getData('assurant_total')){
            $order->setData('assurant_total', $shippingAddress->getData('assurant_total'));
        }
        else{
            $billingAddress = $quote->getBillingAddress();
            $order->setData('assurant_total', $billingAddress->getData('assurant_total'));
        }
        $order->save();
    }

    public function saveAssurantTotalForMultishipping(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $address = $observer->getEvent()->getAddress();
        $order->setData('assurant_total', $shippingAddress->getData('assurant_total'));
        $order->save();
    }
}
