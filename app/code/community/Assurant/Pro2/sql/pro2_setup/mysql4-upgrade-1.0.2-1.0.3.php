<?php
$installer = $this;

$installer->startSetup();
$sql = "create table {$installer->getTable('pro2/aproduct')} (
    id int not null auto_increment,
    name varchar(255),
    description text null,
    price decimal(10,2),
    match_max decimal(11,2),
    match_min decimal(11,2),
    assurant_id int(11),
    assurant_category_id varchar(255),
    pib_url text,
    avb_url text,
    max_period integer(3),
    default_option_id integer(2),
    insurance_company varchar(255),
    options_hash text,
    matches varchar(255),
    last_synced_at datetime,
    active boolean default false,
    primary key(id)
)";

$installer->run($sql);

$sql="create table {$installer->getTable('pro2/acategory')} (
    id int not null auto_increment,
    type varchar(255),
    name varchar(255),
    assurant_id int(11),
    matches varchar(255),
    created_at datetime,
    updated_at datetime,
    active boolean default false,
    primary key(id)
)";
$installer->run($sql);

$installer->endSetup();
