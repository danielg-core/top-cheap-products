<?php
/**
 * Copyright © 2018 Marius Grad
 */
namespace Gmc\TopCheapProducts\Model\Config\Source;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Option\ArrayInterface;

/**
 * Source model category
 */
class Category implements ArrayInterface
{
    const PREFIX = "--";
    
    /**
     * Category
     *
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Construct
     *
     * @param CollectionFactory $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository) 
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Return option array
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionArray()
    {
        $category = $this->categoryRepository->get(CategoryModel::TREE_ROOT_ID);
        $options = $this->getCategoryTreeOptionArray($category);
        array_unshift(
                $options, 
                [
                    'label' => __('-- Please Select a Category --'), 
                    'value' => ''
                ]
            );
        return $options;
    }
    
    /**
     * Recursively traverse the category tree and return all categories
     * 
     * @param CategoryModel $category
     * @param int $level
     * @return array
     */
    private function getCategoryTreeOptionArray(
            CategoryModel $category,  
            int $level = 0):array
    {
        $options = [];
        
        if (!$category->hasChildren()) {
            return $options;
        }
        
        foreach ($category->getChildrenCategories() as $child) {
            $options[] = [
                'label' => sprintf(
                        "%s %s", 
                        str_repeat(self::PREFIX, $level), $child->getName()
                    ),
                'value' => $child->getId()
            ];
            
            $options = array_merge(
                    $options, 
                    $this->getCategoryTreeOptionArray($child, $level+1)
                );
        }
        
        return $options;
    }
}
