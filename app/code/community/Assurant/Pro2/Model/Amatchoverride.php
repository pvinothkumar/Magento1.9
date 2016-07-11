<?php
class Assurant_Pro2_Model_Amatchoverride extends Mage_Core_Model_Abstract {

    CONST CODE_DENY = 'deny';

    protected $read;
    protected $write;
    protected $table;

    protected function _construct(){
        $this->_init("pro2/amatchoverride");
        $this->_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->_table = Mage::getSingleton('core/resource')->getTableName('assurant_match_overrides');
    }

    /**
     *  mark matches that was denied manually by merchant
     *
     *  @author atheotsky
     */
    public function updateOverride($mid, $matches, $remove_matches) {
        $clean = array_diff($matches, $remove_matches);
        foreach ($clean as $aid) {
            $binds = ['mp_id' => $mid, 'ap_id' => $aid];
            $this->_write->query("delete from {$this->_table} where mp_id = :mp_id and ap_id = :ap_id", $binds);
        }

        foreach ($remove_matches as $aid) {
            $binds = ['mp_id' => $mid, 'ap_id' => $aid, 'override_code' => self::CODE_DENY];
            $this->_write->query("insert into {$this->_table} (mp_id, ap_id, override_code) values (:mp_id, :ap_id, :override_code) on duplicate key update override_code = override_code", $binds);
        }
    }

    /**
     *  check to see if given assurant product is allowed to link to given magento product
     *
     *  @author atheotsky
     */
    public function linkAllowed($ap, $mp) {
        $binds = ['mp_id' => $mp->getId(), 'ap_id' => $ap->getId(), 'override_code' => self::CODE_DENY];
        $result = $this->_read->query("select id from {$this->_table} where mp_id = :mp_id and ap_id = :ap_id and override_code = :override_code", $binds);

        $row = $result->fetch();
        if (!empty($row)) {
            return false;
        }

        return true;
    }

    /**
     * remove all override of assurant product
     *
     * @author atheotsky
     */
    public function flushOverrideDeny($mp_id, $ac_id) {
        $helper = Mage::helper('pro2/match');
        $mp = Mage::getModel('catalog/product')->load($mp_id);
        foreach ($helper->getAPsbyMP($mp) as $ap) {
            Mage::getSingleton('core/session')->setByPassAssurantLock(true);
            $ap = Mage::getModel('catalog/product')->loadByAttribute('sku', 'assurant_'.$ap->getAssurantId());
            if ($ap->getAssurantCategoryId() == $ac_id) {
                $binds = ['mp_id' => $mp_id, 'ap_id' => $ap->getId(), 'override_code' => self::CODE_DENY];
                $this->_write->query("delete from {$this->_table} where mp_id = :mp_id and ap_id = :ap_id and override_code = :override_code", $binds);
            }
        }
    }

}
