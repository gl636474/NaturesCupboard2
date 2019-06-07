<?php
/**
 * Natures Cupboard 2 Magento extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is copyright Gareth Ladd 2018. Not for public dissemination
 * nor use.
 *
 * DISCLAIMER
 *
 * This program is private software. It comes without any warranty, to
 * the extent permitted by applicable law. You may not copy, modify nor
 * distribute it. The author takes no responsibility for any consequences of
 * unauthorised usage of this file or any part thereof.
 */

/**
 * Note this must be called Gareth_NaturesCupboard2_Model_Observer if
 * it is in the Model directory. To call it 
 * Gareth_NaturesCupboard2_Model_Product_Observer we must put it in
 * a Product subdirectory for the Magento autoload facility to find it.
 * 
 * @author gareth
 */
class Gareth_NaturesCupboard2_Model_Observer extends Varien_Object
{
	/**
	 * @var string  $_storeGroupName The name of the Store Group
	 */
	private static $_storeGroupName = 'Natures Cupboard';
	
	/**
	 * A cache of attribute_code to Category instance which denotes the
	 * categories a product should be added to/removed from if the attribute
	 * is true/false.
	 * 
	 * @var array
	 */
	private static $_mappings = null;
	
	/**
	 * Returns an array of attribute code to category URL key which
	 * represents the categories to which products should be added to if the
	 * product has the specified attribute set to true.
	 */
	protected function getAttributeCodeToCategoryMap()
	{
		if (is_null(self::$_mappings))
		{
			self::$_mappings = array();
			$model = Mage::getModel('gareth_naturescupboard2/attribtocategorymapping');
			$mappingCollection = $model->getCollection();
			/* @var Gareth_NaturesCupboard2_Model_AttribToCategoryMapping $thisMapping */
			foreach ($mappingCollection as $thisMapping)
			{
				$attributeCode = $thisMapping->getAttributeCode();
				$categoryUrlKey = $thisMapping->getCategoryUrlKey();
				
				/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
				$lookup= Mage::helper('gareth_naturescupboard2/lookup');
				$mappedCategory = $lookup->findCategoryByUrlKey(self::$_storeGroupName, $categoryUrlKey);
				if (!empty($mappedCategory))
				{
					self::$_mappings[$attributeCode] = $mappedCategory;
				}
			}
			// garbage collect: clearInstance() does not exist at runtime!
			//$mappingCollection->clearInstance();
		}
		return self::$_mappings;
	}
	
	protected function setProductCategories($product)
	{
		// the categories the product is (currently) in
		$categoryIds = $product->getCategoryIds();
		
		$mappings = $this->getAttributeCodeToCategoryMap();
		/* @var Mage_Catalog_Model_Category $mappedCategory */
		foreach ($mappings as $attributeCode => $mappedCategory)
		{
			$attributeValue = $product->getData($attributeCode);
			if (!is_null($attributeValue))
			{
				// this product has this attribute
				if ($attributeValue)
				{
					// add the mapped category and all ancestors to this product
					$categoryIds[] = $mappedCategory->getId();
					
					$parentCategoryIds = $mappedCategory->getParentIds();
					$categoryIds = array_merge($categoryIds, $parentCategoryIds);
				}
				else
				{
					// remove the mapped category and all descendents from this product
					// getAllChilren returns mappedCategory ID also. true = as array
					$childCategoryIdsToRemove = $mappedCategory->getAllChildren(true);
					// returns $array1 having removed all elements from $array2
					$categoryIds =array_diff($categoryIds, $childCategoryIdsToRemove);
				}
			}
			else
			{
				// this product does not have this attribute - ignore
			}
		}
		
		$categoryIds = array_unique($categoryIds);
		$product->setCategoryIds($categoryIds);
		
		$productName = $product->getName();
		Mage::log('Product '.$productName.' re-assigned categories: '.implode(',',$categoryIds), null, 'gareth.log');
	}
	
	/**
	 * Function called the catalog_product_save_before
	 * observer configured in config.xml
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public function setCategoriesOnProduct($observer)
	{
		/* @var Mage_Catalog_Model_Product $product */
		$product = $observer->getEvent()->getProduct();
		
		Mage::log('setCategoriesOnProduct called on '.$product->getName(), null, 'gareth.log');
		
		$this->setProductCategories($product);
	} 
	
	/**
	 * Function called the catalog_product_import_finish_before
	 * observer configured in config.xml
	 *
	 */
	function setCategoriesOnAllProducts($observer)
	{
		Mage::log('setCategoriesOnAllProducts called', null, 'gareth.log');
		
		$allProducts = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
		/* @var Mage_Catalog_Model_Product $product */
		foreach ($allProducts as $product)
		{
			Mage::log('setting categories on '.$product->getName(), null, 'gareth.log');
			//$this->setProductCategories($product);
			// this triggers catalog_product_save_before observer 
			$product->save();
		}
	
// DOES NOT WORK		
//		/* @var $indexCollection Mage_Index_Model_Resource_Process_Collection */
//		$indexCollection = Mage::getModel('index/process')->getCollection();
//		foreach ($indexCollection as $index)
//		{
//			/* @var $index Mage_Index_Model_Process */
//			$index->reindexAll();
//		}
	}
}
