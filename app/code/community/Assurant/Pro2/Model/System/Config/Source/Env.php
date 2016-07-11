<?php

/**
 * Used in creating options for Environment config value selection
 *
 */
class Assurant_Pro2_Model_System_Config_Source_Env
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'production', 'label'=>Mage::helper('adminhtml')->__('Production')),
            array('value' => 'staging', 'label'=>Mage::helper('adminhtml')->__('Staging')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'production' => Mage::helper('adminhtml')->__('Production'),
            'staging' => Mage::helper('adminhtml')->__('Staging'),
        );
    }

}
