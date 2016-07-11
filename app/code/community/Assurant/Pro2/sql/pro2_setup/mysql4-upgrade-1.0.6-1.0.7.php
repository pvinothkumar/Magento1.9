<?php
$installer = $this;
$installer->startSetup();

$sql="ALTER TABLE {$installer->getTable('pro2/amatchoverride')} ADD UNIQUE INDEX `match_and_code` (`mp_id`, `ap_id`, `override_code`)";
$installer->run($sql);

$installer->endSetup();
