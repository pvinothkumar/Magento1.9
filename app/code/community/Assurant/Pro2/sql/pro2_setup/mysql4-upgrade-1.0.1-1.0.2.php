<?php
$installer = Mage::getResourceModel('sales/setup', 'sales_setup');
$installer->startSetup();

$installer->addAttribute("quote_address", "assurant_total", array("type"=>"varchar"));
$installer->addAttribute("order", "assurant_total", array("type"=>"varchar"));

$installer->addAttribute('quote_item', 'assurant_item_option_id', array('type'=>'int','default'=>null));
$installer->addAttribute('order_item', 'assurant_item_option_id', array('type'=>'int','default'=>null));
$installer->addAttribute('invoice_item', 'assurant_item_option_id', array('type'=>'int','default'=>null));
$installer->addAttribute('shipment_item', 'assurant_item_option_id', array('type'=>'int','default'=>null));
$installer->addAttribute('creditmemo_item', 'assurant_item_option_id', array('type'=>'int','default'=>null));

$installer->addAttribute('quote_item', 'assurant_item_id', array('type'=>'int','default'=>null));
$installer->addAttribute('order_item', 'assurant_item_id', array('type'=>'int','default'=>null));
$installer->addAttribute('invoice_item', 'assurant_item_id', array('type'=>'int','default'=>null));
$installer->addAttribute('shipment_item', 'assurant_item_id', array('type'=>'int','default'=>null));
$installer->addAttribute('creditmemo_item', 'assurant_item_id', array('type'=>'int','default'=>null));

$installer->endSetup();
