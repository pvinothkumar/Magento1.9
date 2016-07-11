<?php
$installer = $this;
$installer->startSetup();

$sql="create table {$installer->getTable('pro2/amatchoverride')} (
    id int not null auto_increment,
    mp_id int(11),
    ap_id int(11),
    override_code varchar(20),
    primary key(id)
)";

$installer->run($sql);
$installer->endSetup();
