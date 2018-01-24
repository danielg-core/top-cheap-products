<?php
/**
 * Copyright © 2018 Marius Grad
 */
namespace Gmc\TopCheapProducts\Block;

use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Block\Product\NewProduct;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\Http\Context;

/**
 * List products block
 * 
 */
class ListProducts extends NewProduct
{
    /**
     * Cunt limit for cheap products system configuration path
     */
    const XML_PATH_TOP_CHEAP_PRODUCT_COUNT = 'topcheapproducts/general/product_count';
    
    /**
     * Category ID for top cheap products system configuration path
     */
    const XML_PATH_TOP_CHEAP_PRODUCT_CATEGORY_ID = 'topcheapproducts/general/category_id';
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $storeConfig;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;
    
    /**
     * @param ProductContext $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param Context $httpContext
     * @param array $data
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductContext $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        Context $httpContext,
        array $data = []) 
    {
        $this->categoryRepository = $categoryRepository;
        $this->storeConfig = $context->getScopeConfig();
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $data
        );
    }
    
    /**
     * Prepare and return product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    protected function _getProductCollection()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_addProductAttributesAndPrices($collection)
                ->addCategoryFilter($this->getCategory())
                ->setPageSize($this->getProductsCount())
                ->setOrder('price', 'asc')
                ->setCurPage(1);
        
        return $collection;
    }
    
    /**
     * Get how much products should be displayed at once.
     *
     * @return int
     */
    public function getProductsCount()
    {
        $this->_productsCount = $this->storeConfig->getValue(self::XML_PATH_TOP_CHEAP_PRODUCT_COUNT);
        
        if (!$this->_productsCount) {
            $this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;
        }
        return $this->_productsCount;
    }
    
    /**
     * Get category set in system configuration
     * 
     * @return Category
     */
    private function getCategory()
    {
        $categoryId = (int)$this->storeConfig->getValue(self::XML_PATH_TOP_CHEAP_PRODUCT_CATEGORY_ID);
        
        if ($categoryId) {
            $category = $this->categoryRepository->get($categoryId);
        } else {
            $category = $this->categoryRepository->get($this->storeManager->getStore()->getRootCategoryId());
        }
        
        return $category;
    }
}
