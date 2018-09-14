<?php

/**
 * Helper functions to find/fetch existing entities by values other
 * than their ID. Just a wrapper for Magento Collections.
 * 
 * @author gareth
 */
class Gareth_NaturesCupboard2_Helper_Lookup extends
Mage_Core_Helper_Abstract
{
	/**
	 * Cached Mage_Core_Model_Store instance. Set/Got by
	 * getStore();
	 */
	private static $_theStore = null;
	
	/**
	 * Returns the store if there is only one store. If there are multiple
	 * stores then returns the store with a name like $_theStoreRegex.
	 * Throws exception if multiple stores and no matching name.
	 *
	 * @param $storeNameRegex a regular expression matching the name of the store to return
	 * @return Mage_Core_Model_Store the first store matching $storeNameRegex
	 */
	public function getStore($storeNameRegex)
	{
		if (is_null(self::$_theStore))
		{
			// Mage::app() is Mage_Core_Model_App
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $storeId => $store)
			{
				$storeName = $store->getGroup()->getName();
				$viewName = $store->getName();
				if (preg_match($storeNameRegex, $storeName))
				{
					self::$_theStore = $store;
				}
			}
			
			if (is_null(self::$_theStore))
			{
				die("Cannot find store with name matching: ".$storeNameRegex);
			}
		}
		
		return self::$_theStore;
	}
	
	/**
	 * Returns the Attribute to Category mapping rule or null if none exists.
	 *
	 * @param string $attribute_code the code of the attribute to look for
	 * @param string $category_url_key the URL of the category to add products to
	 * @return Gareth_NaturesCupboard2_Model_AttribToCategoryMapping the mapping object or null
	 */
	public function findAttributeToCategoryMapping($attribute_code, $category_url_key)
	{
		$mappingCollection = Mage::getModel('attribtocategorymapping/attribtocategorymapping')->getCollection();
		$mappingCollection->addFieldToFilter('attribute_code', $attribute_code);
		$mappingCollection->addFieldToFilter('category_url_key', $category_url_key);
		
		if (count($mappingCollection) > 0)
		{
			$mapping = $mappingCollection->getFirstItem();
			return $mapping;
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Returns the attribute set object with the given name or ID.
	 *
	 * @param int|string $nameOrId name or ID to find
	 *
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function findAttributeSet($nameOrId)
	{
		if (is_numeric($nameOrId))
		{
			$set = Mage::getModel('eav/entity_attribute_set')->load($nameOrId);
			if (is_null($set))
			{
				die('Cannot find AttributeSet with ID: '.$nameOrId);
			}
		}
		elseif (is_string($nameOrId))
		{
			$entityType = Mage::getModel('catalog/product')->getResource()->getTypeId();
			$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter($entityType);
			
			$set = null;
			foreach($collection as $thisSet)
			{
				if (strtolower($nameOrId) == strtolower($thisSet->getAttributeSetName()))
				{
					$set = $thisSet;
					break;
				}
			}
		}
		
		if (!is_null($set) and !is_null($set->getId()))
		{
			return $set;
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Returns the category object with the given name. Only looks within the
	 * specified store.
	 *
	 * @param $storeNameRegex the name of the store to look in (regex or exact match)
	 * @param $name the name of the category to return
	 * @return Mage_Catalog_Model_Category
	 */
	public function findCategory($storeNameRegex, $name)
	{
		$rootCatId = $this->getStore($storeNameRegex)->getRootCategoryId();
		
		$categoriesCollection = Mage::getModel('catalog/category')->getCollection();
		$categoriesCollection->addAttributeToFilter('name', $name);
		$categoriesCollection->addAttributeToFilter('path', array('like' => '%/'.$rootCatId.'/%'));
		
		if (count($categoriesCollection) > 0)
		{
			$category = $categoriesCollection->getFirstItem();
			return $category;
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Returns the category object with the given URL key. Only looks within the
	 * specified store.
	 *
	 * @param $storeNameRegex the name of the store to look in (regex or exact match)
	 * @param $name the name of the category to return
	 * @return Mage_Catalog_Model_Category
	 */
	public function findCategoryByUrlKey($storeNameRegex, $urlKey)
	{
		$rootCatId = $this->getStore($storeNameRegex)->getRootCategoryId();
		
		$categoriesCollection = Mage::getModel('catalog/category')->getCollection();
		$categoriesCollection->addAttributeToFilter('url_key', $urlKey);
		$categoriesCollection->addAttributeToFilter('path', array('like' => '%/'.$rootCatId.'/%'));
		
		if (count($categoriesCollection) > 0)
		{
			$category = $categoriesCollection->getFirstItem();
			return $category;
		}
		else
		{
			return null;
		}
	}
	
}