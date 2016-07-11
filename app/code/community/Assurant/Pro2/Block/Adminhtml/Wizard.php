<?php
/**
 * Wizard helper for Assurant Extension
 *
 * @author atheotsky
 */
class Assurant_Pro2_Block_Adminhtml_Wizard extends Mage_Adminhtml_Block_Widget_Form {

	CONST STEP_CONFIG = 0;
	CONST STEP_MATCH = 1;
	CONST STEP_RESULT = 2;
    protected $_steps = ['config', 'category_match', 'result'];

    /**
     * Class constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
    }
	

	public function setTemplate($template)
	{
		if (!empty($this->_steps[$template]) && $step_file = $this->_steps[$template]) {
			return parent::setTemplate("pro2/wizard/step/{$step_file}.phtml");
		}
		else {
			return parent::setTemplate($template);
		}
	}

    /**
     * return wizard content html
     *
     * @author atheotsky
     */
    public function getWizardContent()
    {
        $container = $this->getLayout()->createBlock('pro2/adminhtml_wizard')->setTemplate('pro2/wizard/step_container.phtml');
        return $container->toHtml();
    }

    /**
     * check to see if we can Load Wizard Or Not
     */
    public function canLoadWizard()
    {
        if (!Mage::getStoreConfig('aintegration/setting/enabled') || is_null(Mage::getStoreConfig('aintegration/setting/wizard_step')) || Mage::getStoreConfig('aintegration/setting/wizard_step') >= count($this->_steps)) {
            return false;
        }

        return true;
    }

    /**
     * get previous step template file
     */
    public function getPrevStep($current)
    {
        $index = array_search($current, $this->_steps);

        if ($index > 0) {
            return $steps[$index - 1];
        }
        return false;
    }
}
