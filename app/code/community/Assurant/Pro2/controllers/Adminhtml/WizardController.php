<?php
/**
 * this Wizard is for merchant
 *
 * @author atheotsky
 */
class Assurant_Pro2_Adminhtml_WizardController extends Mage_Adminhtml_Controller_Action {
    /**
     * get confirm configuraiton & process download Action
     *
     * @author atheotsky
     */
	public function configAction()
	{
        if ($this->getRequest()->isXmlHttpRequest()) {
            $result = array('error' => false, 'output' => '');
            Mage::getConfig()->saveConfig('aintegration/setting/wizard_step', Assurant_Pro2_Block_Adminhtml_Wizard::STEP_CONFIG);
        	$result['output'] = $this->getLayout()->createBlock('pro2/adminhtml_wizard')->setTemplate(Assurant_Pro2_Block_Adminhtml_Wizard::STEP_CONFIG)->toHtml();

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
		
	}

    /**
     * get confirm configuraiton & process download Action
     *
     * @author atheotsky
     */
    public function configconfirmAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $result = array('error' => false, 'output' => '');
            try {
                $data = $this->getRequest()->getPost();
                $back = $this->getRequest()->getParam('back');
                if (!empty($data['auth_token']) || $back) {
                    if (!$back){
                        /* save data to configuration section */
                        //Mage::getConfig()->saveConfig('aintegration/setting/parter_id', $data['parter_id']);
                        //Mage::getConfig()->saveConfig('aintegration/setting/shop_id', $data['shop_id']);
                        Mage::getConfig()->saveConfig('aintegration/setting/auth_token', $data['auth_token']);
                        Mage::getConfig()->cleanCache();
                        Mage::getConfig()->reinit();

                        /* process Assurant refresh download */
                        $cron = Mage::getModel('pro2/cron');
                        $cron->AssurantSync();
                        $cron->downloadContentFeed();
                        $this->progressOutput();
                    }

                    /* return step 2 when finished */
                    Mage::getConfig()->saveConfig('aintegration/setting/wizard_step', Assurant_Pro2_Block_Adminhtml_Wizard::STEP_MATCH);
                    $result['output'] = $this->getLayout()->createBlock('pro2/adminhtml_wizard')->setTemplate(Assurant_Pro2_Block_Adminhtml_Wizard::STEP_MATCH)->toHtml();
                }
                else {
                    Mage::throwException('Authenication Token Missing');
                }
            }
            catch (Exception $e) {
                $result['error'] = true;
                $result['output'] = $e->getMessage();
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * get previous step of wizard
     *
     * @author atheotsky
     */
    public function confirmmatchAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $result = array('error' => false, 'output' => '');
            
            try {
                $data = $this->getRequest()->getPost();
                if (!empty($data['matches'])) {
                    /* save matches */
                    foreach ($data['matches'] as $acid => $matches) {
                        $matches = explode(',', $matches);
                        $matches = ','.implode(',', array_unique($matches)).','; // make sure we have acids splitted by commas

                        Mage::getModel('pro2/acategory')->load($acid)->setMatches($matches)->save();
                    }

                    /* process product match by Assurant Categories */
                    $cron = Mage::getModel('pro2/cron');
                    $cron->matchProducts();
                }

                /* return step 3 when finished */
                Mage::app()->getCacheInstance()->flush(); // clear all caches
                $this->progressOutput();
                Mage::getConfig()->saveConfig('aintegration/setting/wizard_step', Assurant_Pro2_Block_Adminhtml_Wizard::STEP_RESULT);
                $result['output'] = $this->getLayout()->createBlock('pro2/adminhtml_wizard')->setTemplate(Assurant_Pro2_Block_Adminhtml_Wizard::STEP_RESULT)->toHtml();
            }
            catch (Exception $e) {
                $result['error'] = true;
                $result['output'] = $e->getMessage();
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * skip wizard
     *
     * @author atheotsky
     */
    public function endwizardAction()
    {
        Mage::getConfig()->saveConfig('aintegration/setting/wizard_step', Assurant_Pro2_Block_Adminhtml_Wizard::STEP_RESULT+1);
        if ($this->getRequest()->getParam('to_setting')) {
            $this->_redirect('adminhtml/system_config/edit/section/aintegration');
        }
        elseif ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('error' => false, 'output' => 'finished')));
        }
    }

    /**
     * re-initialize wizard
     *
     * @author atheotsky
     */
    public function reinitAction()
    {
        Mage::getConfig()->saveConfig('aintegration/setting/wizard_step', 0);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirectReferer();
        }
    }

    private function progressOutput($msg='') {
        $logFile = Mage::getBaseDir('media') . DS . 'assurant_processing.log';
        file_put_contents($logFile, $msg);
    }
}
