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
 * Product related heper functions. Used by Observer.php and one or more of the
 * data-upgrade-*-*.php scripts.
 *
 * @author gareth
 */
class Gareth_NaturesCupboard2_Helper_Product extends Mage_Core_Helper_Abstract
{
	/**
	 * Returns an array of attribute code to category object which
	 * represents the categories to which products should be added to if the
	 * product has the specified attribute set to true.
	 */
	public function getAttributeCodeToCategoryMap()
	{
		/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		
		$mappings = array();
		$model = Mage::getModel('gareth_naturescupboard2/attribtocategorymapping');
		$mappingCollection = $model->getCollection();
		/* @var Gareth_NaturesCupboard2_Model_AttribToCategoryMapping $thisMapping */
		foreach ($mappingCollection as $thisMapping)
		{
			$attributeCode = $thisMapping->getAttributeCode();
			$categoryId = $thisMapping->getCategoryUId();
			$category = $lookup->findCategoryById($categoryId);
			if (!empty($category))
			{
				$mappings[$attributeCode] = $category;
			}
		}
		return $mappings;
	}
	
	/**
	 * Sets the categorys of the specified Product based upon the values of its
	 * attributes. The attributes looked at and the categories set are defined
	 * by the collection of AttributeToCategoryMapping objects.
	 * 
	 * If a mapped attribute is true, this product will be added to the
	 * associated category and all its ancestors. If the attribute is false, the
	 * product will be removed from that category and all descendants.
	 * 
	 * @param Mage_Catalog_Model_Product $product The product to set categories of
	 */
	public function setCategoriesFromAttributes($product)
	{
		$productName = $product->getName();
		$mappings = $this->getAttributeCodeToCategoryMap();
		$product_attributes = $product->getAttributes();//self::$_attributeSetGroupName, false);
		
		$categoryIds = $product->getCategoryIds();
		/* @var Mage_Catalog_Model_Resource_Eav_Attribute $thisAttribute */
		foreach ($product_attributes as $thisAttribute)
		{
			$thisAttributeCode = $thisAttribute->getAttributeCode();
			$thisAttributeIsMapped = array_key_exists($thisAttributeCode, $mappings);
			if ($thisAttributeIsMapped)
			{
				$mappedCategory = $mappings[$thisAttributeCode];				
				$thisAttributeIsTrue = $product->getData($thisAttributeCode);
				if ($thisAttributeIsTrue)
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
					$categoryIds = array_diff($categoryIds, $childCategoryIdsToRemove);
				}
			}
		}
		
		$categoryIds = array_unique($categoryIds);
		$product->setCategoryIds($categoryIds);
		Mage::log('Product '.$productName.' re-assigned categories: '.implode(',',$categoryIds), null, 'gareth.log');
	}
}