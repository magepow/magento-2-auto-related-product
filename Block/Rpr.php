<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magepow\AutoRelatedProduct\Block;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\LayoutFactory;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Url\EncoderInterface;

/**
 * Catalog Products List widget block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Rpr extends \Magento\Catalog\Block\Product\AbstractProduct implements BlockInterface, IdentityInterface
{
    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 10;

    /**
     * Name of request parameter for page number value
     *
     * @deprecated
     */
    const PAGE_VAR_NAME = 'np';

    /**
     * Default value for products per page
     */
    const DEFAULT_PRODUCTS_PER_PAGE = 5;

    /**
     * Default value whether show pager or not
     */
    const DEFAULT_SHOW_PAGER = false;

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    protected $pager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|null
     */
    private $urlEncoder;

    /**
     * @var \Magento\Framework\View\Element\RendererList
     */
    protected $responsive;
    protected $_ruleFactory;
    private $rendererListBlock;
    private $rprFactory; //add
    private $block_title=NULL;
    private $max_product= 10;
    private $store_id= NULL;
    private $customer_groupid= NULL;
    protected $_customerSession;
    protected $_storeManager;
    private $attribute_name=NULL;
    private $attribute_value=NULL;
    public $position;
    public $_imageHelper;
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param array $data
     * @param Json|null $json
     * @param LayoutFactory|null $layoutFactory
     * @param \Magento\Framework\Url\EncoderInterface|null $urlEncoder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magepow\Core\Model\Config\Source\Responsive $responsive,
        \Magento\CatalogWidget\Model\RuleFactory $ruleFactory,
        \Magepow\AutoRelatedProduct\Model\RprFactory $rprFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\Rule $rule,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = [],
        Json $json = null,
        LayoutFactory $layoutFactory = null,
        EncoderInterface $urlEncoder = null
    ) {
        $this->responsive = $responsive;
        $this->_ruleFactory = $ruleFactory;
        $this->rprFactory = $rprFactory;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->httpContext = $httpContext;
        $this->sqlBuilder = $sqlBuilder;
        $this->rule = $rule;
        $this->conditionsHelper = $conditionsHelper;
        $this->_imageHelper = $imageHelper;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        $this->layoutFactory = $layoutFactory ?: ObjectManager::getInstance()->get(LayoutFactory::class);
        $this->urlEncoder = $urlEncoder ?: ObjectManager::getInstance()->get(EncoderInterface::class);
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG,
            ], ]);
    }

    public function getRprRule(){
        return $collection = $this->rprFactory->create()->getCollection();
    }
    
    /**
     * Get key pieces for caching block content
     *
     * @return array
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getCacheKeyInfo()
    {
        $conditions = $this->getData('conditions')
            ? $this->getData('conditions')
            : $this->getData('conditions_encoded');
        return [
            'CATALOG_PRODUCTS_LIST_WIDGET'.$this->getData('rule_id_use_at_block'),
            $this->getPriceCurrency()->getCurrency()->getCode(),
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            (int) $this->getRequest()->getParam($this->getData('page_var_name'), 1),
            $this->getProductsPerPage(),
            $this->getProductsCount(),
            $conditions,
            $this->json->serialize($this->getRequest()->getParams()),
            $this->getTemplate(),
            $this->getTitle()
        ];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = $priceRender->render(
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            $product,
            $arguments
        );

        return $price;
    }

    /**
     * @inheritdoc
     */
    protected function getDetailsRendererList()
    {
        if (empty($this->rendererListBlock)) {
            /** @var $layout \Magento\Framework\View\LayoutInterface */
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('catalog_widget_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();

            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.details.renderers');
        }
        return $this->rendererListBlock;
    }

    /**
     * Get post parameters.
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($url),
            ]
        ];
    }

    public function getLoadedProductCollection()
    {
        return $this->getProductCollection();
    }

    public function getTypeFilter(){
        return $this->getData('block_position');
    }
    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->createCollection());
        return parent::_beforeToHtml();
    }

    /**
     * Prepare and return product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function createCollection()
    {
        $collection = NULL;
        $myRules = $this->getRprRule();
        if($myRules!=NULL){
            $choose_rule_to_apply = $this->getData('rule_id_use_at_block');
            /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $collection = $this->productCollectionFactory->create();
                $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

                $collection = $this->_addProductAttributesAndPrices($collection)
                    ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1));
                $displayitems=$this->getDisplayItem($myRules, $choose_rule_to_apply);
                $this->setConfig($myRules, $choose_rule_to_apply);
                if(!isset($displayitems)||count($displayitems)<1)return NULL;
                $collection->addAttributeToFilter('entity_id', array('in' => $displayitems));
                $collection->setPageSize($this->getMaxProduct())->addAttributeToSort($this->getAttributeName(), $this->getAttributeValue());
                if($this->getAttributeName()==NULL){
                    $collection->getSelect()->order('rand()');
                }
                $collection->setPageSize($this->getMaxProduct())->addStoreFilter();
                $collection->distinct(true);
        }
        return $collection;
    }

    protected function getRule($conditions)
    {
        $rule = $this->_ruleFactory->create();
        if(is_array($conditions)) $rule->loadPost($conditions);
        return $rule;
    }

    public function getDisplayItem($myRules, $ruleId){
        $condition_array = null;
        foreach ($myRules as $rule) {
            if($rule->getData('rule_id') == $ruleId){
                $condition_rule = $this->getRule(@unserialize($rule->getData('display_item')));
                $collection = $this->productCollectionFactory->create();
                $conditions = $condition_rule->getConditions();
                $conditions->collectValidatedAttributes($collection);
                $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
                if($collection){
                    foreach ($collection as $product)
                        {
                            $condition_array[]=$product->getData('entity_id');
                        }
                }
                $tmp = explode(';', $rule->getData('block_settings'));
                $this->setMaxProduct($tmp[2]);
                $this->setBlockTitle($tmp[0]);
                $this->setStoreViewId(explode(',', $rule->getData('store_id')));
                $this->setCustomerGroupId(explode(',', $rule->getData('customer_group_id')));
                $this->setAttributetoFilterFromRule($tmp[1]);
            }
        }
        return $condition_array;
    }

    public function getSlideOptions()
    {
        /* Magepow\CategoriesPro\Model\Rule $slide_option and 'responsive' */
        return array('slide', 'vertical', 'vertical-Swiping', 'infinite', 'autoplay', 'arrows', 'dots', 'fade', 'center-Mode', 'rows', 'speed', 'autoplay-Speed', 'padding', 'responsive');
    }

    public function setConfig($totalRules ,$ruleId){
        foreach ($totalRules as $rule) {
            // echo '<pre>';
            if($rule->getData('rule_id') == $ruleId){
                $tmp1 = explode(' || ', $rule->getData('config'));
                $tmp1_1 = explode(';',$tmp1[0]);
                $tmp1_2 = explode(';',$tmp1[1]);
                $count=0;
                $responsive = '[';
                foreach ($this->responsive->getBreakpoints() as $key => $value) {
                    if(isset($tmp1_1[$count])){
                        if(($key-1)==1920){
                            $this->setData('slides-To-Show',$tmp1_1[$count]);
                        }
                        $responsive.='{"breakpoint":'.'"'.($key-1).'", "settings": {"slidesToShow": "'.$tmp1_1[$count].'"}}';
                        $count+=1;
                    }
                    if($key!=361&&$key!=1)$responsive.=', ';
                }
                $responsive .= ']';
                $count=0;
                foreach ($this->rprFactory->create()->slide_option as $key){
                    if(isset($tmp1_2[$count])){
                        $this->setData($key,$tmp1_2[$count]);
                        if(!empty($this->getData('vertical'))){
                            if($this->getData('vertical')==="false" && $key=="vertical-Swiping")$this->setData($key,"false");
                            else if($this->getData('vertical')=="true"&&$key=="fade")$this->setData($key,"false");
                        }
                        $count+=1;
                    }
                }
                $this->setData('responsive', $responsive);
            }
        }
    }

    public function getHeading() 
    {
        return $this->getBlockTitle();
    }  

    public function getWidgetCfg(){
        return array('cart'=>$this->getData('cart'), 'compare'=>$this->getData('compare'), 'wishlist'=>$this->getData('wishlist'), 'review'=>$this->getData('review'));
    }

    public function getFrontendCfg(){
        if($this->getSlide()) return $this->getSlideOptions();

        return array('padding', 'responsive');
    }

    public function setBlockTitle($block_title){
        $this->block_title=$block_title;
    }
    public function getBlockTitle(){
        return $this->block_title;
    }

    public function setMaxProduct($max_product){
        $this->max_product=$max_product;
    }
    public function getMaxProduct(){
        return $this->max_product;
    }

    public function setStoreViewId($store_id){
        $this->store_id=$store_id;
    }
    public function getStoreViewId(){
        return $this->store_id;
    }

    public function setCustomerGroupId($customer_groupid){
        $this->customer_groupid = $customer_groupid;
    }
    public function getCustomerGroupId(){
        return $this->customer_groupid;
    }

    public function setAttributetoFilterFromRule($id_touse){
        if($id_touse==2){
            $this->attribute_name='price';
            $this->attribute_value='desc';
        }elseif($id_touse==3){
            $this->attribute_name='price';
            $this->attribute_value='asc';
        }elseif($id_touse==4){
            $this->attribute_name='create_at';
            $this->attribute_value='desc';
        }elseif($id_touse==1){
            $this->attribute_name='name';
            $this->attribute_value='asc';
        }
    }

    public function getAttributeName(){
        return $this->attribute_name;
    }
    public function getAttributeValue(){
        return $this->attribute_value;
    }
    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if ($this->hasData('products_count')) {
            return $this->getData('products_count');
        }

        if (null === $this->getData('products_count')) {
            $this->setData('products_count', self::DEFAULT_PRODUCTS_COUNT);
        }

        return $this->getData('products_count');
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsPerPage()
    {
        if (!$this->hasData('products_per_page')) {
            $this->setData('products_per_page', self::DEFAULT_PRODUCTS_PER_PAGE);
        }
        return $this->getData('products_per_page');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Retrieve how many products should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        return $this->showPager() ? $this->getProductsPerPage() : $this->getProductsCount();
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->showPager() && $this->getProductCollection()->getSize() > $this->getProductsPerPage()) {
            if (!$this->pager) {
                $this->pager = $this->getLayout()->createBlock(
                    \Magento\Catalog\Block\Product\Widget\Html\Pager::class,
                    $this->getWidgetPagerBlockName()
                );

                $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getData('page_var_name'))
                    ->setLimit($this->getProductsPerPage())
                    ->setTotalLimit($this->getProductsCount())
                    ->setCollection($this->getProductCollection());
            }
            if ($this->pager instanceof \Magento\Framework\View\Element\AbstractBlock) {
                return $this->pager->toHtml();
            }
        }
        return '';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getProductCollection()) {
            foreach ($this->getProductCollection() as $product) {
                if ($product instanceof IdentityInterface) {
                    $identities = array_merge($identities, $product->getIdentities());
                }
            }
        }

        return $identities ?: [\Magento\Catalog\Model\Product::CACHE_TAG];
    }

    /**
     * Get value of widgets' title parameter
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Get currency of product
     *
     * @return PriceCurrencyInterface
     * @deprecated 100.2.0
     */
    private function getPriceCurrency()
    {
        if ($this->priceCurrency === null) {
            $this->priceCurrency = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(PriceCurrencyInterface::class);
        }
        return $this->priceCurrency;
    }

    /**
     * @inheritdoc
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        $requestingPageUrl = $this->getRequest()->getParam('requesting_page_url');

        if (!empty($requestingPageUrl)) {
            $additional['useUencPlaceholder'] = true;
            $url = parent::getAddToCartUrl($product, $additional);
            return str_replace('%25uenc%25', $this->urlEncoder->encode($requestingPageUrl), $url);
        }

        return parent::getAddToCartUrl($product, $additional);
    }

    /**
     * Get widget block name
     *
     * @return string
     */
    private function getWidgetPagerBlockName()
    {
        $pageName = $this->getData('page_var_name');
        $pagerBlockName = 'widget.products.list.pager';

        if (!$pageName) {
            return $pagerBlockName;
        }

        return $pagerBlockName . '.' . $pageName;
    }
}
