<?php

$installer = $this;

/**
 * Install product link types
 */
$data = array(
    array(
        'link_type_id'  => Assurant_Pro2_Model_Catalog_Product_Link::LINK_TYPE_ASSURANT,
        'code'          => 'assurant_pro2'
    )
);

foreach ($data as $bind) {
    $installer->getConnection()->insertForce($installer->getTable('catalog/product_link_type'), $bind);
}

/**
 * Install product link attributes
 */
$data = array(
    array(
        'link_type_id'                  => Assurant_Pro2_Model_Catalog_Product_Link::LINK_TYPE_ASSURANT,
        'product_link_attribute_code'   => 'position',
        'data_type'                     => 'int'
    )
);

$installer->getConnection()->insertMultiple($installer->getTable('catalog/product_link_attribute'), $data);
