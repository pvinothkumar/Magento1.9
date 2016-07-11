<?php
  
$installer = $this;
$installer->startSetup();
$installer->run("
  
DROP TABLE IF EXISTS {$this->getTable('brand')};
CREATE TABLE {$this->getTable('brand')} (
  `brand_id` int(11) unsigned NOT NULL auto_increment,
  `brand_attr_id` int(11) unsigned NOT NULL ,
  `title` varchar(255) NOT NULL default '',
  `filename` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `position` varchar(255) NULL,
  `shown_frontend` varchar(255) NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
    ");
  
$installer->endSetup(); 
