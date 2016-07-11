<?php
$installer = $this;
$installer->startSetup();

$sql="ALTER TABLE {$installer->getTable('pro2/aproduct')} ADD INDEX `assurant_category_id` (`assurant_category_id`)";
$installer->run($sql);

$sql="ALTER TABLE {$installer->getTable('pro2/aproduct')} ADD INDEX `assurant_id` (`assurant_id`)";
$installer->run($sql);

$sql="ALTER TABLE {$installer->getTable('pro2/aproduct')} ADD INDEX `price` (`price`)";
$installer->run($sql);

$sql="ALTER TABLE {$installer->getTable('pro2/acategory')} ADD INDEX `matches` (`matches`)";
$installer->run($sql);

$sql="ALTER TABLE {$installer->getTable('pro2/acategory')} ADD INDEX `assurant_id` (`assurant_id`)";
$installer->run($sql);

$installer->endSetup();
