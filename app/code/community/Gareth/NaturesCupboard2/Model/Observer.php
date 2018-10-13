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
	 * Returns an array of attribute code to category URL key which
	 * represents the categories to which products should be added to if the
	 * product has the specified attribute set to true.
	 */
	protected function getAttributeCodeToCategoryUrlKeyMapping()
	{
		$mappings = array();
		$model = Mage::getModel('attribtocategorymapping/attribtocategorymapping');
		$mappingCollection = $model->getCollection();
		/* @var Gareth_NaturesCupboard2_Model_AttribToCategoryMapping $thisMapping */
		foreach ($mappingCollection as $thisMapping)
		{
			$attributeCode = $thisMapping->getAttributeCode();
			$categoryUrlKey = $thisMapping->getCategoryUrlKey();
			
			$mappings[$attributeCode] = $categoryUrlKey;
		}
		return $mappings;
	}
	
	protected function setProductCategories($product)
	{
		$productName = $product->getName();
		$mappings = $this->getAttributeCodeToCategoryUrlKeyMapping();
		$product_attributes = $product->getAttributes();//self::$_attributeSetGroupName, false);

		$categoryIds = array();
		/* @var Mage_Catalog_Model_Resource_Eav_Attribute $thisAttribute */
		foreach ($product_attributes as $thisAttribute)
		{
			$thisAttributeCode = $thisAttribute->getAttributeCode();
			$thisAttributeIsMapped = array_key_exists($thisAttributeCode, $mappings);
			$thisAttributeIsTrue = $product->getData($thisAttributeCode);
			if ($thisAttributeIsMapped && $thisAttributeIsTrue)
			{
				$categoryUrlKeyToAddTo = $mappings[$thisAttributeCode];
				
				/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
				$lookup= Mage::helper('gareth_naturescupboard2/lookup');
				/** @var Mage_Catalog_Model_Category $categoryToAddTo */
				$categoryToAddTo = $lookup->findCategoryByUrlKey(self::$_storeGroupName, $categoryUrlKeyToAddTo);
				if (!is_null($categoryToAddTo))
				{
					$categoryId = $categoryToAddTo->getId();
					
					$parentCategoryIds = $categoryToAddTo->getParentIds();
					$categoryIds[] = $categoryId;
					$categoryIds = array_merge($categoryIds, $parentCategoryIds);
				}
			}
		}
		
		$categoryIds = array_unique($categoryIds);
		$product->setCategoryIds($categoryIds);
		Mage::log('Product '.$productName.' re-assigned categories: '.implode(',',$categoryIds), null, 'gareth.log');
	}
	
	/**
	 * Function called the catalog_product_save_before
	 * observer configured in config.xml
	 * 
	 * @param unknown $observer
	 */
	public function setCategoriesOnProduct($observer)
	{
		/* @var Mage_Catalog_Model_Product $product */
		$product = $observer->getEvent()->getProduct();
		
		Mage::log('setCategoriesOnProduct called on '.$product->getName(), null, 'gareth.log');
		
		$this->setProductCategories($product);
		
		// TODO flush cache if made any changes ?
	
	} 
	
	/**
	 * Function called the catalog_product_import_finish_before
	 * observer configured in config.xml
	 *
	 * @param unknown $observer
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
