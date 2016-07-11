<?php
/**
 * Assurant categories match tab
 *
 * @category   Assurant
 * @package    Assurant_Adminhtml
 * @author     atheotsky
 */
class Assurant_Pro2_Block_Adminhtml_Acategory_Edit_Tab_Match extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected $_categoryIds;
    protected $_selectedNodes = null;

    /**
     * Specify template to use
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('pro2/tabs/categories.phtml');
    }

    /**
     * Retrieve currently edited product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getAcategory()
    {
        return Mage::registry('acategory_data');
    }

    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getAcategory()->getCategoriesReadonly();
    }

    /**
     * Return array with category IDs which the product is assigned to
     *
     * @return array
     */
    protected function getCategoryIds()
    {
        $categoryIds = array();
        if ($this->getAcategory()) {
            $categoryIds = array_unique(array_merge($categoryIds, explode(',', $this->getAcategory()->getMatches())));
        }
        foreach ($categoryIds as $key=>$value) if (empty($value)) unset($categoryIds[$key]);

        return array_unique($categoryIds);
    }

    /**
     * Forms string out of getCategoryIds()
     *
     * @return string
     */
    public function getIdsString()
    {
        return implode(',', $this->getCategoryIds());
    }

    /**
     * Returns root node and sets 'checked' flag (if necessary)
     *
     * @return Varien_Data_Tree_Node
     */
    public function getRootNode()
    {
        $root = $this->getRoot();
        if ($root && in_array($root->getId(), $this->getCategoryIds())) {
            $root->setChecked(true);
        }
        return $root;
    }

    /**
     * Returns root node
     *
     * @param Mage_Catalog_Model_Category|null $parentNodeCategory
     * @param int                              $recursionLevel
     * @return Varien_Data_Tree_Node
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = Mage::registry('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');

            if ($storeId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            }
            else {
                $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
            }

            $ids = $this->getSelectedCategoriesPathIds($rootId);
            $tree = Mage::getResourceSingleton('catalog/category_tree')
                ->loadByIds($ids, false, false);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
                if ($this->isReadonly()) {
                    $root->setDisabled(true);
                }
            }
            elseif($root && $root->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            Mage::register('root', $root);
        }

        return $root;
    }

    /**
     * Returns array with configuration of current node
     *
     * @param Varien_Data_Tree_Node $node
     * @param int                   $level How deep is the node in the tree
     * @return array
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = parent::_getNodeJson($node, $level);

        if ($this->_isParentSelectedCategory($node) || $this->hasChildrenChecked($node)) {
            $item['expanded'] = true;
        }

        if (in_array($node->getId(), $this->getCategoryIds())) {
            $item['checked'] = true;
        }

        if ($this->isReadonly()) {
            $item['disabled'] = true;
        }

        return $item;
    }

    /**
     * open category if there is children checked
     */
    private function hasChildrenChecked($node)
    {
        $children = Mage::getModel('catalog/category')->load($node->getEntityId())->getAllChildren();
        $children = explode(',', $children);
        foreach ($this->getCategoryIds() as $id)
        {
            if (in_array($id, $children)) return true;
        }
        return false;
    }

    /**
     * Returns whether $node is a parent (not exactly direct) of a selected node
     *
     * @param Varien_Data_Tree_Node $node
     * @return bool
     */
    protected function _isParentSelectedCategory($node)
    {
        $result = false;
        // Contains string with all category IDs of children (not exactly direct) of the node
        $allChildren = $node->getAllChildren();
        if ($allChildren) {
            $selectedCategoryIds = $this->getCategoryIds();
            $allChildrenArr = explode(',', $allChildren);
            for ($i = 0, $cnt = count($selectedCategoryIds); $i < $cnt; $i++) {
                $isSelf = $node->getId() == $selectedCategoryIds[$i];
                if (!$isSelf && in_array($selectedCategoryIds[$i], $allChildrenArr)) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns array with nodes those are selected (contain current product)
     *
     * @return array
     */
    protected function _getSelectedNodes()
    {
        if ($this->_selectedNodes === null) {
            $this->_selectedNodes = array();
            $root = $this->getRoot();
            foreach ($this->getCategoryIds() as $categoryId) {
                if ($root) {
                    $this->_selectedNodes[] = $root->getTree()->getNodeById($categoryId);
                }
            }
        }

        return $this->_selectedNodes;
    }

    /**
     * Returns JSON-encoded array of category children
     *
     * @param int $categoryId
     * @return string
     */
    public function getCategoryChildrenJson($categoryId)
    {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $node = $this->getRoot($category, 1)->getTree()->getNodeById($categoryId);

        if (!$node || !$node->hasChildren()) {
            return '[]';
        }

        $children = array();
        foreach ($node->getChildren() as $child) {
            $children[] = $this->_getNodeJson($child);
        }

        return Mage::helper('core')->jsonEncode($children);
    }

    /**
     * Returns URL for loading tree
     *
     * @param null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('*/*/categoriesJson', array('_current' => true));
    }

    /**
     * Return distinct path ids of selected categories
     *
     * @param mixed $rootId Root category Id for context
     * @return array
     */
    public function getSelectedCategoriesPathIds($rootId = false)
    {
        $ids = array();
        $categoryIds = $this->getCategoryIds();
        if (empty($categoryIds)) {
            return array();
        }
        $collection = Mage::getResourceModel('catalog/category_collection');

        if ($rootId) {
            $collection->addFieldToFilter('parent_id', $rootId);
        } else {
            $collection->addFieldToFilter('entity_id', array('in'=>$categoryIds));
        }

        foreach ($collection as $item) {
            if ($rootId && !in_array($rootId, $item->getPathIds())) {
                continue;
            }
            foreach ($item->getPathIds() as $id) {
                if (!in_array($id, $ids)) {
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }
}
