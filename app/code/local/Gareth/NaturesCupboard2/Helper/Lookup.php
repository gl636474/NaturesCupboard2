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
 * Helper functions to find/fetch existing entities by values other
 * than their ID. Just a wrapper for Magento Collections.
 * 
 * @author gareth
 */
class Gareth_NaturesCupboard2_Helper_Lookup extends
Mage_Core_Helper_Abstract
{	
	/**
	 * Determines whether the given string would be a valid store code. Valid 
	 * store codes contain lowercase letters, numbers and underscore, start with
	 * a letter and are at least one character long.
	 * 
	 * @param string $code the string to test
	 * @return true if $code would be a valid store code, false otherwise
	 */
	protected function isValidStoreCode($code)
	{
		return preg_match('/^[a-z][a-z0-9_]*$/', $code);
	}
	
	/**
	 * Returns the store (i.e. store view) with the specified id/name.
	 * Optionally performs a regex match on the store's name. If no such store
	 * exists, returns null.
	 *
	 * @param integer|string|Mage_Core_Model_Store $key the store id or code or name or name regex of the store to return
	 * @param boolean $regex whether $key is a regular expression matching the name of the store to return
	 * @return Mage_Core_Model_Store the specified store or null
	 */
	public function getStore($key, $regex = false)
	{
		if (is_numeric($key))
		{
			$store = Mage::app()->getStore($key);
			if (is_null($store))
			{
				Mage::log('Cannot find store with id: '.$key, Zend_Log::NOTICE, 'gareth.log');
			}
		}
		else if (is_string($key))
		{
			$found = false;
			if (!$found)
			{
				// Mage::app() is Mage_Core_Model_App
				$allStores = Mage::app()->getStores();
				$allStores = array_values($allStores);
				foreach ($allStores as $store)
				{
					$storeCode = $store->getCode();
					$storeViewName = $store->getName();
					if ($storeCode==$key)
					{
						$found = true;
						break;
					}
					elseif ($regex && preg_match($key, $storeViewName))
					{
						$found = true;
						break;
					}
					elseif (!$regex && $storeViewName==$key)
					{
						$found = true;
						break;
					}
				}
			}
			
			if (!$found)
			{
				$store = null;
				Mage::log('Cannot find store with name: '.$key, Zend_Log::NOTICE, 'gareth.log');
			}
			//else $store correctly set in previous for loop
		}
		else if ($key instanceof Mage_Core_Model_Store)
		{
			$store = $key;
		}
		else
		{
			$store = null;
			Mage::log('Unknown argument passed to getStore(): '.$key, Zend_Log::WARN, 'gareth.log');
		}
		
		return $store;
	}
	
	/**
	 * Returns the store group with the specified id/name. Optionally performs a
	 * regex match on the group name. If no such group exists, returns null.
	 *
	 * @param integer|string|Mage_Core_Model_Store|Mage_Core_Model_Store_Group $key the group id or name or name regex of the store group to return
	 * @param boolean $regex whether $key is a regular expression matching the name of the store group to return
	 * @return Mage_Core_Model_Store_Group the specified store group or null
	 */
	public function getStoreGroup($key, $regex = false)
	{
		if (is_numeric($key))
		{
			$storeGroup = Mage::app()->getGroup($key);
			if (is_null($storeGroup))
			{
				Mage::log('Cannot find store group with id: '.$key, Zend_Log::NOTICE, 'gareth.log');
			}
		}
		else if (is_string($key))
		{
			$found = false;
			// Mage::app() is Mage_Core_Model_App
			$allGroups = Mage::app()->getGroups();
			$allGroups = array_values($allGroups);
			foreach ($allGroups as $storeGroup)
			{
				$storeGroupName = $storeGroup->getName();
				if ($regex && preg_match($key, $storeGroupName))
				{
					$found = true;
					break;
				}
				elseif (!$regex && $storeGroupName==$key)
				{
					$found = true;
					break;
				}
			}
			
			if (!$found)
			{
				$storeGroup = null;
				Mage::log('Cannot find store group with name: '.$key, Zend_Log::NOTICE, 'gareth.log');
			}
			//else $storeGroup correctly set in previous for loop
		}
		else if ($key instanceof Mage_Core_Model_Store_Group)
		{
			$storeGroup = $key;
		}
		else if ($key instanceof Mage_Core_Model_Store)
		{
			$storeGroup = $key->getGroup();
		}
		else
		{
			$storeGroup = null;
			Mage::log('Unknown argument passed to getStore(): '.$key, Zend_Log::WARNING, 'gareth.log');
		}
		
		return $storeGroup;
	}
	
	/**
	 * Returns the website with the specified name. If no such website exists,
	 * returns null.
	 * 
	 * @param string $websiteName the name of the website to return
	 * @return Mage_Core_Model_Website the requested website or null if not found
	 */
	public function getWebsite($websiteName)
	{
		$websiteCollection = Mage::getModel('core/website')->getCollection();
		$websiteCollection->addFieldToFilter('name',$websiteName);
		if (count($websiteCollection) > 0)
		{
			/** @var Mage_Core_Model_Website $website */
			$website = $websiteCollection->getFirstItem();
		}
		else
		{
			$website = null;
		}
		return $website;
	}

	/**
	 * Returns the Attribute to Category mapping rule or null if none exists.
	 *
	 * @param string $attribute_code
	 *        	the code of the attribute to look for
	 * @param string $category_url_key
	 *        	the URL of the category to add products to
	 * @return Gareth_NaturesCupboard2_Model_AttribToCategoryMapping the mapping
	 *         object or null
	 */
	public function findAttributeToCategoryMapping ($attribute_code, $category_url_key)
	{
		$mappingModel = Mage::getModel('gareth_naturescupboard2/attribtocategorymapping');
		if (! empty($mappingModel))
		{
			$mappingCollection = $mappingModel->getCollection();
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
		else
		{
			Mage::log('Cannot get model gareth_naturescupboard2/attribtocategorymapping', Zend_Log::ERR, 'gareth.log');
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
	 * Returns the category object with the specified atribute having the
	 * specified value. Only looks within the specified store.
	 *
	 * @param integer|string|Mage_Core_Model_Store|Mage_Core_Model_Store_Group $storeKey the store or group or id or name or name regex of the store to return
	 * @param string $attribute the categry attribute to seach by
	 * @param string $value the value of $attribute to look for
	 * @return Mage_Catalog_Model_Category
	 */
	protected function findCategory($storeKey, $attribute, $value)
	{
		$storeGroup = $this->getStoreGroup($storeKey);
		$rootCatId = $storeGroup->getRootCategoryId();
		
		$categoriesCollection = Mage::getModel('catalog/category')->getCollection();
		$categoriesCollection->addAttributeToFilter($attribute, $value);
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
	 * Returns the category object with the given name. Only looks within the
	 * specified store.
	 *
	 * @param integer|string|Mage_Core_Model_Store|Mage_Core_Model_Store_Group $storeKey the store or group or id or name or name regex of the store to return
	 * @param string $name the name of the category to return
	 * @return Mage_Catalog_Model_Category
	 */
	public function findCategoryByName($storeKey, $name)
	{
		return $this->findCategory($storeKey, 'name', $name);
	}
	
	/**
	 * Returns the category object with the given URL key. Only looks within the
	 * specified store.
	 *
	 * @param integer|string|Mage_Core_Model_Store|Mage_Core_Model_Store_Group $storeKey the store or group or id or name or name regex of the store to return
	 * @param string $urlKey the URL key of the category to return
	 * @return Mage_Catalog_Model_Category
	 */
	public function findCategoryByUrlKey($storeKey, $urlKey)
	{
		return $this->findCategory($storeKey, 'url_key', $urlKey);
	}	
}