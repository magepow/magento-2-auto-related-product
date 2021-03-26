<?php
namespace Magepow\AutoRelatedProduct\Block;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Widget\Model\Layout\UpdateFactory;
use Magepow\AutoRelatedProduct\Model\Options\BlockPosition;
/**
 *  AddCategoryLayoutUpdateHandleObserver
 */
class AddXml extends \Magento\CatalogRule\Model\Rule implements ObserverInterface
{
    const CONTENT_TEMPLATE = 'Magepow_AutoRelatedProduct::slider.phtml';

    const RELATED_NAME = 'catalog.product.related';

    const UPSELL_NAME = 'product.info.upsell';

    const CROSSEL_NAME = 'checkout.cart.crosssell';

    const TAB_NAME = 'product.info.details';

    private $rprFactory;
    
    protected $productCollectionFactory;
    protected $_ruleFactory;
    protected $sqlBuilder;
    public $storeManager;
    public $_customerSession;
    public $json;
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magepow\AutoRelatedProduct\Model\RprFactory $rprFactory,
        \Magento\CatalogWidget\Model\RuleFactory $ruleFactory,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Serialize\Serializer\Json $json
    ){
        $this->productCollectionFactory = $productCollectionFactory;
        $this->rprFactory = $rprFactory;
        $this->_ruleFactory = $ruleFactory;
        $this->sqlBuilder   = $sqlBuilder;
        $this->storeManager     = $storeManager;
        $this->_customerSession = $customerSession;
        $this->json = $json;
    }

    /**
     * Add handles to the page.
     *
     * @param Observer $observer
     * @event layout_load_before
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var LayoutInterface $layout */
        $layout = $observer->getData('layout');
        /** Get current object type*/
        $RprRules = $this->rprFactory->create()->getCollection();
        $currentObjectType = NULL;
        $currentObjectId = NULL;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currentProduct = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
        $currentCategory = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
        $currentCart = $objectManager->create('Magento\Framework\UrlInterface')->getCurrentUrl();
        if($currentProduct!=NULL){
            $currentObjectType = 'product';
            $currentObjectId = $currentProduct->getId();
        }elseif($currentCategory!=NULL){
            $currentObjectType = 'category';
            $currentObjectId = $currentCategory->getData('entity_id');
        }elseif(strlen(strstr($currentCart, 'cart')) > 0){
            $currentObjectType = 'cart';
        }
        $rule_array = $this->getFittedRuleArray($RprRules, $currentObjectType, $currentObjectId);
        $layoutUpdate = $layout->getUpdate();
        $arrRuleDisplayInTab = array();
        foreach ($rule_array as $key => $value) {
            if($value==='product_content_tab'){
                $arrRuleDisplayInTab[$key] = $value;
                continue;
            }
            $block_position = $value;
            $rule_id = $key;
            $nameInLayout = 'rpr_' . $block_position.'_'.$rule_id;
            $xml = '<referenceContainer name="content.top">
                <block class="Magepow\AutoRelatedProduct\Block\Rpr" name="'.$nameInLayout.'" template="'.self::CONTENT_TEMPLATE.'">
                    <arguments>
                        <argument name="rule_id_use_at_block" xsi:type="string">'.$rule_id.'</argument>
                        <argument name="block_position" xsi:type="string">'.$block_position.'</argument>
                    </arguments>
                </block>
            </referenceContainer>
            <move element="'.$nameInLayout.'" destination="'.$this->getContainerByPosition($block_position).'" '.$this->getPositionAttribute($block_position).'/>';
            $layoutUpdate->addUpdate($xml);
        }
        foreach ($arrRuleDisplayInTab as $key => $value) {
            $block_position = $value;
            $rule_id = $key;
            $nameInLayout = 'rpr_' . $block_position;
            $xml = '<referenceContainer name="content">
                    <referenceBlock name="product.info.details">
                    <block class="Magepow\AutoRelatedProduct\Block\Rpr" name="'.$nameInLayout.'" template="Magepow_AutoRelatedProduct::slider.phtml" group="detailed_info">
                        <arguments>
                            <argument name="block_position" xsi:type="string">'.$block_position.'</argument>
                            <argument name="rule_id_use_at_block" xsi:type="string">'.$rule_id.'</argument>
                            <argument name="sort_order" xsi:type="string">100</argument>
                            <argument translate="true" name="title" xsi:type="string">Related Products</argument>
                        </arguments>
                    </block>
                    </referenceBlock>
                </referenceContainer>';
            $layoutUpdate->addUpdate($xml);
        }
    }


    public function getFittedRuleArray($RprRules, $objectType, $currentObject_Id){
        $rule_id_to_check = array();
        $rule_id_block_position = array();
        if($objectType == 'cart'){
            foreach ($RprRules as $rule) {
                if(strlen(strstr($rule->getData('block_position'), 'cart')) > 0 && $this->checkStatusAndStoreViewAndCustomerGroup($rule)){
                    if(array_key_exists($rule->getData('block_position'), $rule_id_block_position) &&
                        $rule->getData('priority') > $rule_id_to_check[$rule_id_block_position[$rule->getData('block_position')]]){
                        unset($rule_id_to_check[$rule_id_block_position[$rule->getData('block_position')]]);
                        $rule_id_to_check[$rule->getData('rule_id')] = $rule->getData('priority');
                        $rule_id_block_position[$rule->getData('block_position')] = $rule->getData('rule_id');
                    }elseif(!array_key_exists($rule->getData('block_position'), $rule_id_block_position)){
                        $rule_id_to_check[$rule->getData('rule_id')] = $rule->getData('priority');
                        $rule_id_block_position[$rule->getData('block_position')] = $rule->getData('rule_id');
                    }
                }
            }
        }elseif($objectType == 'product'){
            foreach ($RprRules as $rule) {
                if(strlen(strstr($rule->getData('block_position'), 'product')) > 0 && $this->getDisplayPlace($rule)!=NULL && in_array($currentObject_Id, $this->getDisplayPlace($rule)) && $this->checkStatusAndStoreViewAndCustomerGroup($rule)){
                    if(array_key_exists($rule->getData('block_position'), $rule_id_block_position) &&
                        $rule->getData('priority') > $rule_id_to_check[$rule_id_block_position[$rule->getData('block_position')]]){
                        unset($rule_id_to_check[$rule_id_block_position[$rule->getData('block_position')]]);
                        $rule_id_to_check[$rule->getData('rule_id')] = $rule->getData('priority');
                        $rule_id_block_position[$rule->getData('block_position')] = $rule->getData('rule_id');
                    }elseif(!array_key_exists($rule->getData('block_position'), $rule_id_block_position)){
                        $rule_id_to_check[$rule->getData('rule_id')] = $rule->getData('priority');
                        $rule_id_block_position[$rule->getData('block_position')] = $rule->getData('rule_id');
                    }
                }
            }
        }elseif($objectType == 'category'){
            foreach ($RprRules as $rule) {
                if(strlen(strstr($rule->getData('block_position'), 'category')) > 0 && $rule->getData('display_to_category')!=NULL && in_array($currentObject_Id, explode(",",$rule->getData('display_to_category'))) && $this->checkStatusAndStoreViewAndCustomerGroup($rule)){
                    if(array_key_exists($rule->getData('block_position'), $rule_id_block_position) &&
                        $rule->getData('priority') > $rule_id_to_check[$rule_id_block_position[$rule->getData('block_position')]]){
                        unset($rule_id_to_check[$rule_id_block_position[$rule->getData('block_position')]]);
                        $rule_id_to_check[$rule->getData('rule_id')] = $rule->getData('priority');
                        $rule_id_block_position[$rule->getData('block_position')] = $rule->getData('rule_id');
                    }elseif(!array_key_exists($rule->getData('block_position'), $rule_id_block_position)){
                        $rule_id_to_check[$rule->getData('rule_id')] = $rule->getData('priority');
                        $rule_id_block_position[$rule->getData('block_position')] = $rule->getData('rule_id');
                    }
                }
            }
        }

        foreach ($rule_id_to_check as $key => $value) {
            foreach ($rule_id_block_position as $key2 => $value2) {
                if($key == $value2){
                    $rule_id_to_check[$key] = $key2;
                    break;
                }
            }
        }
        return $rule_id_to_check;
    }

    public function checkStatusAndStoreViewAndCustomerGroup($rule){
        $status = $rule->getData('status');
        if(!$status)return false;
        $this->setData('stores_id',explode(',', $rule->getData('store_id')));
        $storeviewname= $this->storeManager->getStore()->getName();
        $count_store_name_fit=0;
        if(in_array(0, $this->getData('stores_id'))){
            $count_store_name_fit=1;
        }else{
            foreach ($this->getData('stores_id') as $storeviewid) {
                if($this->storeManager->getStore($storeviewid)->getName()==$storeviewname)$count_store_name_fit++;
            }
        }
        if($count_store_name_fit==0)return false;
        $customerGroup= NULL;
        if($this->_customerSession->isLoggedIn()){
            $customerGroup=$this->_customerSession->getCustomer()->getGroupId();
        }
        $CustomerGroupId = explode(',', $rule->getData('customer_group_id'));
        if($CustomerGroupId!=NULL&&!in_array(0, $CustomerGroupId)&&!in_array($customerGroup, $CustomerGroupId)){
            return false;
        }
        return true;
    }

    protected function getRule($conditions)
    {
        $rule = $this->_ruleFactory->create();
        if(is_array($conditions)) $rule->loadPost($conditions);
        return $rule; 
    }

    public function getDisplayPlace($rule){
        $productID_where_display = NULL;
        $display_place = $rule->getData('display_place');
        ($display_place!=NULL)? $display_place = $this->json->unserialize($display_place) : NULL;
        $display_place = array('conditions' => $display_place);
        $rule = $this->getRule($display_place);
        $collection = $this->productCollectionFactory->create();
        $conditions = $rule->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
        if($collection){
            foreach ($collection as $product)
                {
                    $productID_where_display[]=$product->getData('entity_id');
                }
        }
        return $productID_where_display;
    }

    private function getPositionAttribute($position)
    {
        switch ($position) {
            case BlockPosition::PRODUCT_AFTER_UPSELL:
                $positionAttribute = 'after="' . self::UPSELL_NAME . '"';
                break;
            case BlockPosition::PRODUCT_AFTER_RELATED:
                $positionAttribute = 'after="' . self::RELATED_NAME . '"';
                break;
            case BlockPosition::PRODUCT_BEFORE_RELATED:
                $positionAttribute = 'before="' . self::RELATED_NAME . '"';
                break;
            case BlockPosition::PRODUCT_BEFORE_TAB:
                $positionAttribute = 'before="' . self::TAB_NAME . '"';
                break;
            case BlockPosition::PRODUCT_BEFORE_UPSELL:
                $positionAttribute = 'before="' . self::UPSELL_NAME . '"';
                break;
            case BlockPosition::CART_AFTER_CROSSSEL:
                $positionAttribute = 'after="'.self::CROSSEL_NAME.'"';
                break;
            case BlockPosition::CART_BEFORE_CROSSSEL:
                $positionAttribute = 'before="'.self::CROSSEL_NAME.'"';
                break;
            case BlockPosition::CATEGORY_SIDEBAR_BOTTOM:
            case BlockPosition::PRODUCT_SIDEBAR_BOTTOM:
            case BlockPosition::PRODUCT_CONTENT_BOTTOM:
            case BlockPosition::CATEGORY_CONTENT_BOTTOM:
            case BlockPosition::CART_CONTENT_BOTTOM:
                $positionAttribute = 'after="-"';
                break;
            case BlockPosition::PRODUCT_INTO_RELATED:
            case BlockPosition::PRODUCT_INTO_UPSELL:
            case BlockPosition::CART_CONTENT_TOP:
            case BlockPosition::CATEGORY_CONTENT_TOP:
            case BlockPosition::CATEGORY_SIDEBAR_TOP:
            case BlockPosition::PRODUCT_SIDEBAR_TOP:
            case BlockPosition::PRODUCT_CONTENT_TOP:
                $positionAttribute = 'before="-"';
                break;
            default:
                $positionAttribute = '';
        }

        return $positionAttribute;
    }

    private function getContainerByPosition($position)
    {
        switch ($position) {
            case BlockPosition::PRODUCT_CONTENT_TOP:
            case BlockPosition::CART_CONTENT_TOP:
            case BlockPosition::CATEGORY_CONTENT_TOP:
                $container = 'content.top';
                break;
            case BlockPosition::CART_CONTENT_BOTTOM:
            case BlockPosition::CATEGORY_CONTENT_BOTTOM:
            case BlockPosition::PRODUCT_CONTENT_BOTTOM:
                $container = 'content.bottom';
                break;
            case BlockPosition::CATEGORY_SIDEBAR_BOTTOM:
            case BlockPosition::PRODUCT_SIDEBAR_BOTTOM:
                $container = 'sidebar.additional';
                break;
            case BlockPosition::CATEGORY_SIDEBAR_TOP:
            case BlockPosition::PRODUCT_SIDEBAR_TOP:
                $container = 'sidebar.main';
                break;
            case BlockPosition::PRODUCT_INTO_RELATED:
            case BlockPosition::PRODUCT_INTO_UPSELL:
            case BlockPosition::PRODUCT_AFTER_RELATED:
            case BlockPosition::PRODUCT_BEFORE_RELATED:
            case BlockPosition::PRODUCT_AFTER_UPSELL:
            case BlockPosition::PRODUCT_BEFORE_UPSELL:
                $container = 'content.aside';
                break;
            case BlockPosition::CART_AFTER_CROSSSEL:
            case BlockPosition::CART_BEFORE_CROSSSEL:
                $container = 'content';
                break;
            case BlockPosition::PRODUCT_BEFORE_TAB:
                $container = 'content';
                break;
            default:
                $container = '';
        }
        return $container;
    }
}
