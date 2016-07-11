<?php
/**
 * Assurant_Pro2 extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Assurant
 * @package        Assurant_Pro2
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Assurant front controller
 *
 * @category    Assurant
 * @package     Assurant_Pro2
 */
require_once(Mage::getModuleDir('controllers','Mage_Checkout').DS.'CartController.php');
 
class Assurant_Pro2_SubmitController extends Mage_Checkout_CartController
{
    /**
     * ajax add product to cart. assurant_stamp is visible on PDP within the product submit form
     * @author atheotsky + PR
     */
    public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        if($params['isAjax'] == 1){
            $response = array();
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');
                $assurant = $this->getRequest()->getParam('assurant_product');
                $addCoverage = $this->getRequest()->getParam('add_coverage');

                /**
                 * Check product availability
                 */
                if (!$product) {
                    $response['status'] = 'error';
                    $response['message'] = $this->__('Unable to find Product ID');
                }

                if ($addCoverage) {
                    if(!Mage::getSingleton('checkout/session')->getQuote()->getItemByProduct($product)) {
                        $cart->addProduct($product, $params);
                    }
                }
                else {
                    $cart->addProduct($product, $params);
                }

                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }                    

                $cart->save();
                $this->_getSession()->setCartWasUpdated(true);

                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );
 
                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()){
                        $mainQuoteItem = $cart->getQuote()->getItemByProduct($product);
                            
                        $message = $this->__('You\'ve just added this product to the cart:');
                        $response['message'] = $message;
                        $response['status'] = 'success';
                        $response['redirect'] = Mage::getUrl('checkout/cart');
                        if (!empty($assurant) || ($mainQuoteItem && $mainQuoteItem->getAssurantItemId())) {
                            $response['assurant'] = 'true';
                        }else{
                            $response['assurant'] = 'false';
                        }                        
                    }
                }
            }
            catch (Mage_Core_Exception $e) {
                $msg = "";
                if ($this->_getSession()->getUseNotice(true)) {
                    $msg = $e->getMessage();
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $msg .= $message.'<br/>';
                    }
                }
 
                $response['status'] = 'error';
                $response['message'] = $msg;
                $response['assurant'] = 'false';
            }
            catch (Exception $e) {
                $response['status'] = 'error';
                $response['assurant'] = 'false';
                $response['message'] = $this->__('Cannot add the item to shopping cart.');
                Mage::logException($e);

            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        }

    }

    
    /**
     * add Protection Plan by using interstitial. assurant_stamp is visible on PDP within the product submit form
     *
     * @author atheotsky + PR
     */
    public function addcoverageAction()
    {
        $params = $this->getRequest()->getParams();

        $productId = $this->getRequest()->getParam('current_id');
        $assurantProductId = $this->getRequest()->getParam('assurant_id');
        $option_id = $this->getRequest()->getParam('option_id');

        $product = Mage::getModel('catalog/product')->load($productId);
        Mage::helper('pro2')->addAssurantProduct($product, $assurantProductId, $option_id);

        $this->_redirect('checkout/cart');        
        return $this;
    }

    /**
     * add Assurant Item add order review step. click radio to toggle
     *
     * @author atheotsky
     */
    public function reviewaddAction()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $assurant_product = Mage::app()->getRequest()->getPost('assurant_product');
        if (empty($assurant_product)) return;

        list($product_id, $assurantProductId, $option_id) = explode(':', $assurant_product);

        if ($product_id && $assurantProductId && $option_id) {
            $parent_product = Mage::getModel('catalog/product')->load($product_id);
            $quote_item = $quote->getItemByProduct($parent_product);
            if ($quote_item->getAssurantItemId()) {
                $assurantItem = $quote->getItemById($quote_item->getAssurantItemId());
                if ($assurantItem->getAssurantItemOptionId() == $option_id) {
                    $quote->removeItem($assurantItem->getId())->save();
                    $quote_item->setAssurantItemId(null)->save();
                }
                else {
                    $quote->removeItem($assurantItem->getId())->save();
                    Mage::helper('pro2')->addAssurantProduct($parent_product, $assurantProductId, $option_id);
                }
            }
            else {
                Mage::helper('pro2')->addAssurantProduct($parent_product, $assurantProductId, $option_id);
            }
        }

        echo 'success';
    }

    /**
     * clean assurant items in cart
     *
     * @author atheotsky
     */
    public function cleanassurantAction() {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if($item->getAssurantItemId()){
                $assurantItem = $quote->getItemById($item->getId());
                $assurantItem->setAssurantItemId(null);
                $quote->removeItem($item->getAssurantItemId())->save();
            }
        }

        $this->_redirect('checkout/cart');
    }

}
