<?php
class Chandan_Freeshipping_Model_Observer 
{ 
  
   // SKUs of Product X and Product Y
   private $fsprod1 = "m-001";
   private $fsprod2 = "m-002";  
   
   // A method which searches for Product X
   // and Product Y within the CART
   private function ifFreeShippingApplicable()
   {
     // Define an SKU array
     $sku_list = array();
    
     // GET CART Items
     $quote = Mage::getSingleton('checkout/session')->getQuote();
     $cartItems = $quote->getAllVisibleItems();
   
     // LOOP Thru Cart Items
     foreach ($cartItems as $item)
     {
        $productId = $item->getProductId();
        $product = Mage::getModel('catalog/product')->load($productId);
        
        // Insert the Product SKU into that array
        $sku_list[] = $product->getSku() ;
     }
   
     // Check if Products X, Y are in the Cart
     if(in_array($this->fsprod1,$sku_list) && in_array($this->fsprod2,$sku_list))
     {
       return true;
     }
     else
     { 
       return false;
     }
    
   }
   
   // Show message that FreeShipping will
   // be applied on Cart
   public function checkfreeshipping(Varien_Event_Observer $observer) 
   { 
      if( $this->ifFreeShippingApplicable() )   
      {  
        // Product X and Y Found, 
        // Hence show Message
        Mage::getSingleton('checkout/session')->addSuccess("You will get Free Shipping at final checkout");
      }
   }
   
   // Method for applying the 'Freeshipping' method
   public function applyfreeshipping(Varien_Event_Observer $observer) 
   {
     // Check for Product X & Y
     //if( $this->ifFreeShippingApplicable() )
     //{
        $quote              = $observer->getEvent()->getQuote();
        $store              = Mage::app()->getStore($quote->getStoreId());
        $carriers           = Mage::getStoreConfig('carriers', $store);

        echo $hiddenMethodCode   = 'freeshipping'; 
        Mage::log($carriers, null, 'trackapi.log');
        foreach ($carriers as $carrierCode => $carrierConfig) 
        {
            if( $carrierCode ==  $hiddenMethodCode )
            {
                $store->setConfig("carriers/{$carrierCode}/active", '0');
            }
        }
       
     //}
   }  
}
?> 
