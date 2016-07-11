<?php
class Assurant_Pro2_Model_Cron{	

    /**
     * output current processing item to var/log/assurant_processing.log
     * if $msg = false , remove output
     *
     * @author atheotsky
     */
    private function progressOutput($msg='') {
        $logFile = Mage::getBaseDir('media') . DS . 'assurant_processing.log';
        file_put_contents($logFile, $msg);
    }

    /**
     * Sync Assurant Categories and Products. this is for wizard
     *
     * @author atheotsky
     */
    public function AssurantSync(){
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        /*sync categories*/
        $this->syncCategories();
        /*sync products*/
        $this->syncProducts();
        
        /*empty assurant_processing.log*/
        $this->progressOutput();
    } 

    /**
     * perform incremental sync content from Assurant. run each 2-3 hours
     *
     * @author atheotsky
     */
    public function AssurantIncrementalSync() {
        if (Mage::getStoreConfig('aintegration/setting/auth_token')) {
            try {
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $this->syncCategories();
                $ap_ids = $this->syncProducts(true);
                $this->downloadContentFeed();
                $this->matchProducts($ap_ids); 
                // make sure we have module enabled
                if (!Mage::getStoreConfig('aintegration/setting/enabled')) {
                    Mage::getConfig()->saveConfig('aintegration/setting/enabled', 1);
                    Mage::app()->getCacheInstance()->flush();    
                }
            }
            catch (Exception $e) {
                // disable module if token is invalid
                if ($e->getMessage() == Assurant_Pro2_Model_Connect_Curl::INVALID_TOKEN_MSG) {
                    if (Mage::getStoreConfig('aintegration/setting/enabled')) {
                        Mage::getConfig()->saveConfig('aintegration/setting/enabled', 0);
                        Mage::app()->getCacheInstance()->flush();  // clear all caches automatically after disabling
                    }
                }
                Mage::logException($e);
            }
        }
    }

    /**
     * perform Sync on Assurant Products
     *
     * @author atheotsky
     */
    public function syncProducts($incremental = false) {
        $this->progressOutput("Downloading Assurant Products");
        $curl = Mage::getModel("pro2/connect_curl");
        $products = $curl->getProducts();
        $product_count = count($products);
        $ap_ids = array(); // array for changed or new Assurant Products

        $downloaded_ids = array();
        foreach ($products as $key=>$p) {
            $downloaded_ids[] = $p->id;
            $this->progressOutput("Procesing Product {$key}/{$product_count} : Assurant Protection Plan {$p->name} ..");
            // create/update assurant_products table to keep track of changes
            $model = Mage::getModel('pro2/aproduct')->load($p->id, 'assurant_id');

            if ($incremental
                && $p->max_price == $model->getPrice()
                && $p->max_price == $model->getMatchMax()
                && $p->min_price == $model->getMatchMin()
                && $model->getOptionsHash() == serialize($p->options)) continue; // ignore if there is no change in incremental mode

            try {
                $model->setName($p->name);
                $model->setAssurantId($p->id);
                $model->setData('assurant_category_id', $p->category->id);
                $model->setData('price', $p->max_price);
                $model->setData('match_min', $p->min_price);
                $model->setData('match_max', $p->max_price);
                $model->setData('options_hash', serialize($p->options));
                $model->setData('insurance_company', "{$p->insurance_company->id}:{$p->insurance_company->name}");
                $model->setData('default_option_id', $p->default_option_id);
                $model->setData('max_period', $p->max_period);
                $model->setData('pib_url', $p->pib_url);
                $model->setData('avb_url', $p->avb_url);
                $model->setDescription($p->description);
                $model->setLastSyncedAt(date('y-m-d', time()));
                $model->setActive(true);

                if ($model->getAssurantId()) {  // only save it when it refers to an assurant product id
                    $model->save();
                }
            }
            catch (Exception $e) {
                Mage::logException($e);
            }

            if ($model->getAssurantProductId()) $ap_ids[] = $model->getAssurantProductId();
        }
        
        $remove_products = Mage::getModel('pro2/aproduct')->getCollection();
        if (count($downloaded_ids)) {
            $remove_products->addFieldToFilter('assurant_id', array('nin' => $downloaded_ids));
        }
        foreach ($remove_products as $p) {
            $this->progressOutput("Removing Deleted Assurant Product {$p->getName()} and associated Magento products");
            $p->delete();
        }

        if ($incremental) return $ap_ids;
    }

    /**
     * match Assurant Products to Magento Products when we Assurant Categories have been matched to Magento Categories.
     * This cron job can be called by wizard, and accept assurant product ids array as filter
     *
     * @param $ap_ids
     * @author atheotsky
     */
    public function matchProducts($ap_ids=[]) {
        Mage::getSingleton('core/session')->setByPassAssurantLock(true);
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $attribute_set_id = $setup->getAttributeSetId('catalog_product', Assurant_Pro2_Model_Aproduct::SET_NAME);

        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('attribute_set_id', array('eq' => $attribute_set_id));

        if (count($ap_ids)) {
            $collection->addAttributeToFilter('entity_id', array('in' => $ap_ids));
        }

        $counter = 0;
        foreach ($collection as $ap) {
            $counter++;
            $this->progressOutput("Matching Assurant Product {$counter}/{$collection->getSize()} : Assurant Protection Plan for {$ap->getName()}");
            Mage::helper('pro2/match')->matchAP($ap);
        }
    }

    /**
     * sync Assurant Categories with Magento
     *
     * @author atheotsky
     */
    public function syncCategories() {
        $this->progressOutput("Downloading Assurant Categories");
        $categories = Mage::getModel("pro2/connect_curl")->getCategories();
        Mage::log($categories, null, 'assurant_api.log');
        $category_count = count($categories);

        $downloaded_ids = array();
        foreach ($categories as $key=>$c) {
            $downloaded_ids[] = $c->id;
            $this->progressOutput("Procesing Category {$key}/{$category_count} : Assurant Category {$c->name} ..");
            $model = Mage::getModel('pro2/acategory');
            $model->load($c->id, 'assurant_id'); // fix duplciate issue
            try {
                $model->setName($c->name);
                $model->setType("{$c->type}:");
                $model->setAssurantId($c->id);
                $model->setCreatedAt(date('y-m-d', time()));
                $model->setUpdatedAt(date('y-m-d', time()));
                $model->setActive(true);

                if ($model->getAssurantId()) { // only save it when it refers to an assurant category id
                    $model->save();
                }
            }
            catch (Exception $e) {
                Mage::logException($e);
            }
        }
        
        /* make sure it delete assurant categories that is not from Assurant API */
        $remove_categories = Mage::getModel('pro2/acategory')->getCollection();
        if (count($downloaded_ids)) {
            $remove_categories->addFieldToFilter('assurant_id', array('nin' => $downloaded_ids));
        }
        foreach ($remove_categories as $c) {
            $this->progressOutput("Removing Assurant Category {$c->getName()} and associated products, matches");
            $c->delete();
        }
    }

    /**
     * download content feed daily from XML file specified by Assurant.
     *
     * @author atheotsky
     */
    public function downloadContentFeed()
    {
        $this->progressOutput("Downloading Assurant XML Feed");
        $env = Mage::getStoreConfig('aintegration/setting/environment');
        $url = Mage::getStoreConfig("aintegration/setting/assurant_content_feed_{$env}");
        if (strstr($url, 'http') === FALSE) {
            $url = Mage::getStoreConfig(Mage_Core_Model_Url::XML_PATH_SECURE_URL).$url;
        }

        $xml = simplexml_load_file($url);

        if ($xml) {
            foreach ($xml->cms->children() as $key => $value) {
                Mage::getConfig()->saveConfig("aintegration/setting/{$key}", $value);
            }

            foreach ($xml->plan_attributes->children() as $key => $value) {
                Mage::getConfig()->saveConfig("aintegration/setting/{$key}", $value);
            }

            foreach ($xml->system_settings->children() as $key => $value) {
                Mage::getConfig()->saveConfig("aintegration/setting/{$key}", $value);
            }
        }
    }
}
