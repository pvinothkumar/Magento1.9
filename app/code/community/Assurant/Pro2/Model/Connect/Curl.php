<?php
/**
 * Connect to Assurant API via Curl
 */
class Assurant_Pro2_Model_Connect_Curl {

    const CREATE_CERTIFICATE = 'transactions/create';
    const CATEGORY_RESOURCE = 'categories';
    const PRODUCT_GROUP_RESOURCE = 'product-groups';

    const REQUEST_POST = 'post';

    const INVALID_TOKEN_MSG = 'Invalid Authentication Token';
    const NOT_FOUND_MSG = 'Not Found';

    /*setting variables for class*/
    protected $_enabled;
    protected $_ws;
    protected $_auth_token;

    /**
     * construct basic information for this class
     */
    public function __construct() {
        $this->_enabled = Mage::getStoreConfig('aintegration/setting/enabled');
        $env = Mage::getStoreConfig('aintegration/setting/environment');
        $this->_ws = Mage::getStoreConfig("aintegration/setting/web_service_{$env}");
        $this->_auth_token = Mage::getStoreConfig('aintegration/setting/auth_token');
    }

    /**
     * build API URL for request
     *
     * @return string
     * @author atheotsky
     */
    private function buildApiUrl($resouce, $id=null) {
        $url = $this->_ws . $resouce;
        if ($id) $url .= "/{$id}";

        return $url;
    }

    /**
     * process request as curl, return what it get
     *
     * @author atheotsky
     */
    protected function request($url, $type = null, $data = null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, $type == self::REQUEST_POST);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'sisu-auth-token: ' . $this->_auth_token
        ));

        if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        // only process response code 200 and 201 . that's all success codes we get from assurant for now
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200 || curl_getinfo($curl, CURLINFO_HTTP_CODE) == 201) {
            $body = substr($response, $header_size);
            $body = json_decode($body);
        }
        elseif(curl_getinfo($curl, CURLINFO_HTTP_CODE) == 403) {
            Mage::throwException(self::INVALID_TOKEN_MSG);
        }
        else {
            Mage::throwException(self::NOT_FOUND_MSG);
        }
        curl_close($curl);

        return $body;
    }

    /**
     * get categories from Assurant
     *
     * @author atheotsky
     */
    public function getCategories() {
        $url = $this->buildApiUrl(self::CATEGORY_RESOURCE);

        return $this->request($url);
    }

    public function getProducts()
    {
        $url = $this->buildApiUrl(self::PRODUCT_GROUP_RESOURCE);

        return $this->request($url);
    }

    /**
     * Create new insurance , post to Assurant
     *
     * @var Mage_Sales_Model_Order $order
     * @author atheotsky
     */
    public function createInsurance($order) {
        $url = $this->buildApiUrl(self::CREATE_CERTIFICATE);
        $data = array();

        $data["customer"] = array();
        $data["customer"]["email"] = $order->getCustomerEmail();
        $data["customer"]["firstName"] = $order->getCustomerFirstname();
        $data["customer"]["lastName"] = $order->getCustomerLastname();
        $data["customer"]["dateOfBirth"] = $order->getCustomerDob();
        $data["customer"]["street"] = $order->getShippingAddress()->getStreetFull();
        $data["customer"]["zip"] = $order->getShippingAddress()->getPostcode();
        $data["customer"]["city"] = $order->getShippingAddress()->getCity();
        $data["customer"]["country"] = $order->getShippingAddress()->getCountry();
        $data["customer"]["state"] = Mage::getModel('directory/region')->load($order->getShippingAddress()->getRegionId())->getCode();
        //$data["customer"]["language"] = Mage::app()->getLocale()->getLocaleCode();;
        //only accept 0 - 9 for phone number
        $data["customer"]["phone"] = preg_replace('/[^0-9]/', '', $order->getShippingAddress()->getTelephone());

        $allItems = $order->getAllItems();
        foreach ($allItems as $item) {
            if (Mage::getModel('eav/entity_attribute_set')->load($item->getProduct()->getAttributeSetId())->getAttributeSetName() == Assurant_Pro2_Model_Aproduct::SET_NAME) {
                list($placeholder, $assurant_id) = explode('_', $item->getSku());
                if ($placeholder != 'assurant' || empty($assurant_id)) continue;

                $data["productGroup"] = $assurant_id;
                $data["productGroupOption"] = array();
                $data["productGroupOption"]["id"] = $item->getAssurantItemOptionId();
                $data["identifiers"] = array();
                $data["identifiers"][0] = array();

                //$data .= "&identifiers[0][serialNumber]=" . $product->getSerialNumber();
                list($name, $sku) = $this->getParentProductName($allItems, $item);
                $data["identifiers"][0]["name"] = $name;
                $data["identifiers"][0]["sku"] = $sku;
                $data["identifiers"][0]["orderNumber"] = $order->getIncrementId();
                $data["identifiers"][0]["price"] = $item->getPrice();

                //Mage::log($data, null, 'assurant_api.log');
                // add support for multiple qty
                for ($i = 0; $i < intval($item->getQtyOrdered()); $i++ ) {
                    $this->request($url, self::REQUEST_POST, http_build_query($data));
                }
            }
        }
    }

    /**
     * get product that current Assurant Product belongs to
     *
     * @author atheotsky
     */
    private function getParentProductName($collection, $assurant_item)
    {
        foreach ($collection as $item)
        {
            if ($item->getAssurantItemId() == $assurant_item->getQuoteItemId()) {
                $product = $item->getProduct();
                return array($product->getName(), $product->getSku());
            }
        }

        // if when parent item not found
        $product = $assurant_item->getProduct();
        return array($product->getName(), $product->getSku());
    }
}
