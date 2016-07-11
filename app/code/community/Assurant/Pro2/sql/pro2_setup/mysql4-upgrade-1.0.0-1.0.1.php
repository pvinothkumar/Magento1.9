<?php
# create new attribute set and add attributes to attribute set
$installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup();
$installer->startSetup();

/*add new attribute set */
$originalSetId = Mage::getModel('catalog/product')->getDefaultAttributeSetId();

$entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
$attributeSet = Mage::getModel('eav/entity_attribute_set')->setEntityTypeId($entityTypeId)->setAttributeSetName(Assurant_Pro2_Model_Aproduct::SET_NAME);
$attributeSet->validate();
$attributeSet->save();
$attributeSet->initFromSkeleton($originalSetId)->save();

/*add attributes to new attribute set*/
$group_name = 'general';
$attributes = array(
    'avb_url',
    'pib_url',
    'max_period',
    'default_option_id',
    'insurance_company',
    'options_hash',
    'assurant_category_id',
    'match_min',
    'match_max',
);

$attribute_set_id = $installer->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);
$attribute_group_id = $installer->getAttributeGroupId('catalog_product', $attribute_set_id, $group_name);

foreach ($attributes as $a) {
    $attribute_id = $installer->getAttributeId('catalog_product', $a);
    $installer->addAttributeToSet('catalog_product' ,$attribute_set_id, $attribute_group_id, $attribute_id);
}

/* remove assurant attributes from other attribute sets */
$attribute_sets = Mage::getResourceModel('eav/entity_attribute_set_collection')->load();

foreach ($attribute_sets as $set) {
    if ($set->getAttributeSetId() != $attribute_set_id) {
        foreach ($attributes as $a) {
            $attribute_id = $installer->getAttributeId('catalog_product', $a);
            $installer->deleteTableRow('eav/entity_attribute', 'attribute_id', $attribute_id, 'attribute_set_id', $set->getAttributeSetId() );
        }
    }
}

$installer->endSetup();
