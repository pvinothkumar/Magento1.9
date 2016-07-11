<?php
# create new attribute set and add attributes to attribute set
$installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup();
$installer->startSetup();

$installer->addAttribute("catalog_product", "avb_url",  array(
    "type"     => "text",
    "label"    => "AVB Url",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,

));

$installer->addAttribute("catalog_product", "pib_url",  array(
    "type"     => "text",
    "label"    => "PIB Url",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
));

$installer->addAttribute("catalog_product", "max_period",  array(
    "type"     => "int",
    "label"    => "Max Period",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
));

$installer->addAttribute("catalog_product", "default_option_id",  array(
    "type"     => "int",
    "label"    => "Default Option Id",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
));

$installer->addAttribute("catalog_product", "insurance_company",  array(
    "type"     => "text",
    "label"    => "Insurance company",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
));

$installer->addAttribute("catalog_product", "options_hash",  array(
    "type"     => "text",
    "label"    => "Options hash",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
));

$installer->addAttribute("catalog_product", "assurant_category_id",  array(
    "type"     => "int",
    "label"    => "Assurant Category Id",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
));

$installer->addAttribute("catalog_product", "match_min",  array(
    "input"     => "price",
    "type"     => "decimal",
    "label"    => "Assurant Match Min Price",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
));

$installer->addAttribute("catalog_product", "match_max",  array(
    "input"     => "price",
    "type"     => "decimal",
    "label"    => "Assurant Match Max Price",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "is_visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
));

$installer->endSetup();
