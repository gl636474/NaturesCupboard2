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
 * Observer to set add/remove a product to/from categories depending upon the
 * values of particular attributes of that product.
 * 
 * The methods in this class is fully load the mapped categories, use the
 * Mage_Catalog_Model_Category functions to add/remove one or many products then
 * call save() on the categories at the end. This means we do not have to load()
 * and save() each product (which takes a lot of time if mass updating say 50 
 * or more products). There are not any methods on the
 * Mage_Catalog_Model_Resource_Product class which could fast-update a product's
 * categories without loading the entire product. Calling save() on a category
 * will also trigger a reindex (which we need for any product-category changes 
 * to take effect). We do however use getAttributeRawValue() of
 * Mage_Catalog_Model_Resource_Product to fast-load attributes without loading
 * the entire product. Note that load()ing a product may not actually load some
 * EAV attributes anyway!
 *
 * Note that the Magento Import Controller invalidates the following indexes
 * post import regardless (this list is statically defined):
 * <ul>
 * <li>catalog_product_price</li>
 * <li>catalog_category_product</li>
 * <li>catalogsearch_fulltext</li>
 * <li>catalog_product_flat</li>
 * </ul>
 * 
 * Requires the following in config.xml:
 *  <adminhtml>
        <events>
            <catalog_product_save_before>
                <observers>
                    <Gareth_NaturesCupboard2_Model_Product_Save_Observer>
                    	<type>singleton</type>
                        <class>Gareth_NaturesCupboard2_Model_Observer_Categories</class>
                        <method>setCategoriesOnProduct</method>
                    </Gareth_NaturesCupboard2_Model_Product_Save_Observer>
                </observers>
            </catalog_product_save_before>
            <catalog_product_import_finish_before>
                <observers>
                    <Gareth_NaturesCupboard2_Model_Product_Save_Observer>
                    	<type>singleton</type>
                        <class>Gareth_NaturesCupboard2_Model_Observer_Categories</class>
                        <method>setCategoriesOnAllProducts</method>
                    </Gareth_NaturesCupboard2_Model_Product_Save_Observer>
                </observers>
            </catalog_product_import_finish_before>
        </events>
    </adminhtml>
 * 
 * @author gareth
 */
class Gareth_NaturesCupboard2_Model_Observer_Categories extends Varien_Object
{
	/**
	 * @var string  $_storeGroupName The name of the Store Group
	 */
	private static $_storeGroupName = 'Natures Cupboard';
	
	/**
	 * Returns an array of attribute code to category. This represents the
	 * categories to which a product should be added to if the product has the
	 * specified attribute set to true.
	 */
	protected function getAttributeCodeToCategoryMap()
	{
		$mappings = array();
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
				$mappings[$attributeCode] = $mappedCategory;
			}
		}
		return $mappings;
	}
	
	/**
	 * Returns the products_position values for the categories in the specified
	 * mappings. In English, this returns the products (by ID) in each category
	 * (by ID). The root category will always be in the returned array.
	 *  
	 * @param array $mappings an array of mapped_attribute_code => category as returned by getAttributeCodeToCategoryMap()
	 * @return array an array category_id => array(product_id => position) which is the current product positions for each category
	 */
	protected function getCategoryProducts(array $mappings)
	{
		$categoryProducts = array();
		/* @var Mage_Catalog_Model_Category $mappedCategory */
		foreach ($mappings as $mappedCategory)
		{
			$categoryId = $mappedCategory->getId();
			$productsPositions = $mappedCategory->getProductsPosition();
			
			$categoryProducts[$categoryId] = $productsPositions;
		}
		
		// Now get root category
		
		/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
		$constants= Mage::helper('gareth_naturescupboard2/constants');
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		/* @var Mage_Core_Model_Store $store */
		$store = $lookup->getStore($constants->getNCStoreViewCode());
		/* @var Mage_Catalog_Model_Category $rootCategory */
		$rootCategory = $lookup->getRootCategory($store);
		
		$rootCategoryProductsPositions = $rootCategory->getProductsPosition();
		$categoryProducts[$rootCategory->getId()] = $rootCategoryProductsPositions;
		
		return $categoryProducts;
	}
	
	/**
	 * Inspects the specified product for the attributes in the specified array
	 * and returns an array of those attributes the product has together with
	 * their values.
	 * 
	 * @param array $mappings an array of mapped_attribute_code => category as returned by getAttributeCodeToCategoryMap()
	 * @param Mage_Catalog_Model_Product $product
	 * @return array an array attrib_code=>attrib_vaue which is the attributes from $mappings the product has together with their values
	 */
	protected function getMappedAttributesAndValuesFromProduct(array $mappings, Mage_Catalog_Model_Product $product)
	{
		$attribs_and_values = array();
		$attributeCodes = array_keys($mappings);
		foreach ($attributeCodes as $code)
		{
			$data = $product->getData($code);
			if (!is_null($data))
			{
				$attribs_and_values[$code] = $data;
			}
		}
		return $attribs_and_values;
	}
	
	/**
	 * Inspects a product in the Datbase (using getAttributeRawValue()) for the
	 * attributes in the specified array and returns an array of those
	 * attributes the product has together with their values.
	 *
	 * @param array $mappings an array of mapped_attribute_code => category as returned by getAttributeCodeToCategoryMap()
	 * @param integer $productId the ID of the product whose attributes to inspect
	 * @return array an array attrib_code=>attrib_vaue which is the attributes from $mappings the product has together with their values
	 */
	protected function getMappedAttributesAndValuesFromDatabase(array $mappings, $productId)
	{
		/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
		$constants= Mage::helper('gareth_naturescupboard2/constants');
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		/* @var Mage_Core_Model_Store $store */
		$store = $lookup->getStore($constants->getNCStoreViewCode());
		
		/* @var Mage_Catalog_Model_Resource_Product $resource */
		$resource = Mage::getResourceModel('catalog/product');
		
		// Fast load only those attributes we are interested in. If an attribute
		// does not exist there will be no key for it in the returned array.
		// This method returns an array attrib_code=>value. However if there is
		// only one returned attribute then a scalar is returned: the
		// attribute's value! By also including name, we ensure an array of the
		// form attrib_code=>value will still be returned, even if there was
		// only one mapped attribute.
		$attributeCodes = array_keys($mappings);
		$attributeCodes[] = 'name';
		$attributesValues = $resource->getAttributeRawValue($productId, $attributeCodes, $store);
		
		// don't bother if only name returned
		if (is_array($attributesValues) && count($attributesValues) > 1)
		{
			$productName = $attributesValues['name'];
			Mage::Log('Inspected DB for attribs of product '.$productId.' ('.$productName.')', Zend_Log::DEBUG, 'gareth.log');
			// remove the extra field added for the issue above. unset() is
			// safe if index does not exist
			unset($attributeCodes['name']);
			unset($attributesValues['name']);
			
			return $attributesValues;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Adds a product to a category in the category-products array. Does nothing
	 * if the product was already in the category.
	 * 
	 * @param Mage_Catalog_Model_Product|integer $product product instance or ID to add
	 * @param Mage_Catalog_Model_Category $category category to add the product to
	 * @param array $categoryProducts an array category_id => array(product_id => position) as returned by getCategoryProducts()
	 * @return array updated $categoryProducts
	 */
	protected function addProductToCategory($product, Mage_Catalog_Model_Category $category, array $categoryProducts)
	{
		if (is_numeric($product))
		{
			$productId = $product;
			$productName = 'Product '.$productId;
		}
		elseif ($product instanceof Mage_Catalog_Model_Product)
		{
			$productId = $product->getId();
			$productName = $product->getName();
		}

		$categoryId  = $category->getId();
		
		if (array_key_exists($categoryId, $categoryProducts))
		{
			if (!array_key_exists($productId, $categoryProducts[$categoryId]))
			{
				$categoryProducts[$categoryId][$productId] = 1;
				Mage::log('     Added '.$productName.' to category '.$categoryId.' ('.$category->getName().')', Zend_Log::DEBUG, 'gareth.log');
			}
			else
			{
				Mage::log('     '.$productName.' already in category '.$categoryId.' ('.$category->getName().')', Zend_Log::DEBUG, 'gareth.log');
			}
		}
		else 
		{
			Mage::log('     No such mapped category '.$categoryId.' ('.$category->getName().') when trying to add product: '.$productName, Zend_Log::DEBUG, 'gareth.log');
		}
		return $categoryProducts;
	}
	
	/**
	 * Removes a product from a category in the category-products array. Does
	 * nothing if the product was not in the category.
	 *
	 * @param Mage_Catalog_Model_Product|integer $product product instance or ID to remove
	 * @param Mage_Catalog_Model_Category $category category to remove the product from
	 * @param array $categoryProducts an array category_id => array(product_id => position) as returned by getCategoryProducts()
	 * @return array updated $categoryProducts
	 */
	protected function removeProductFromCategory($product, Mage_Catalog_Model_Category $category, array $categoryProducts)
	{
		if (is_numeric($product))
		{
			$productId = $product;
			$productName = 'Product '.$productId;
		}
		elseif ($product instanceof Mage_Catalog_Model_Product)
		{
			$productId = $product->getId();
			$productName = $product->getName();
		}
		
		
		$categoryId  = $category->getId();
		
		if (array_key_exists($categoryId, $categoryProducts))
		{
			if (array_key_exists($productId, $categoryProducts[$categoryId]))
			{
				unset($categoryProducts[$categoryId][$productId]);
				Mage::log('     Removed '.$productName.' from category '.$categoryId.' ('.$category->getName().')', Zend_Log::DEBUG, 'gareth.log');
			}
			else
			{
				Mage::log('     '.$productName.' already not in category '.$categoryId.' ('.$category->getName().')', Zend_Log::DEBUG, 'gareth.log');
			}
		}
		else
		{
			Mage::log('     No such mapped category '.$categoryId.' ('.$category->getName().') when trying to remove product: '.$productName, Zend_Log::DEBUG, 'gareth.log');
		}
		return $categoryProducts;
	}
		
	/**
	 * Adds or removes the specified product to/from the specified categories
	 * in categoryProducts according to the values of the attributes specified
	 * in attributesValues based upon the attribute-category mappings in 
	 * mappings.
	 * 
	 * @param integer $productId ID of product to add to/remove from the mapped categories 
	 * @param array $attributesValues an array of attribute_code => attribute_value which are the mapped attributes of the specified product
	 * @param array $mappings an array of attribute_code => category_object as returned by getAttributeCodeToCategoryMap()
	 * @param array $categoryProducts an array category_id => array(product_id => position) as returned by getCategoryProducts()
	 * @return array updated $categoryProducts
	 */
	protected function setProductCategories($productId, $attributesValues, $mappings, array $categoryProducts)
	{
		if (!empty($attributesValues))
		{
			foreach ($attributesValues as $attributeCode => $attributeValue)
			{
				/* @var Mage_Catalog_Model_Category $mappedCategory */
				$mappedCategory = $mappings[$attributeCode];
				//Mage::log('     Looking at '.$attributeCode.' (='.$attributeValue.', =>category '.$mappedCategory->getName().')', Zend_Log::DEBUG, 'gareth.log');
				if ($attributeValue)
				{
					$categoryProducts = $this->addProductToCategory($productId, $mappedCategory, $categoryProducts);
				}
				else
				{
					$categoryProducts = $this->removeProductFromCategory($productId, $mappedCategory, $categoryProducts);
				}
			}
		}
		else 
		{
			Mage::log('     No change to categories', Zend_Log::DEBUG, 'gareth.log');
		}
		
		// Ensure product is in the Nature's Cupboard root category (so it
		// appears in the ALL category tab)
		
		/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
		$constants= Mage::helper('gareth_naturescupboard2/constants');
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		/* @var Mage_Core_Model_Store $store */
		$store = $lookup->getStore($constants->getNCStoreViewCode());
		/* @var Mage_Catalog_Model_Category $rootCategory */
		$rootCategory = $lookup->getRootCategory($store);
		
		$categoryProducts = $this->addProductToCategory($productId, $rootCategory, $categoryProducts);
		
		return $categoryProducts;
	}
	
	/**
	 * Calls save() on each of the categories in the specified array.
	 * 
	 * @param array $mappings an array of attrib_code=>category_object as returned by getAttributeCodeToCategoryMap()
	 * @param array $categoryProducts an array category_id => array(product_id => position) as returned by getCategoryProducts()
	 */
	protected function saveMappedCategories(array $mappings, array $categoryProducts)
	{
		// foreach will give us the array values, not the keys
		/* @var Mage_Catalog_Model_Category $mappedCategory */
		foreach ($mappings as $mappedCategory)
		{
			$categoryId = $mappedCategory->getId();
			if (array_key_exists($categoryId, $categoryProducts))
			{
				$productsPosition = $categoryProducts[$categoryId];
				$mappedCategory->setPostedProducts($productsPosition);
				$mappedCategory->save();
				
				$products = array_keys($productsPosition);
				$products = implode(',',$products);
				Mage::log('Category '.$mappedCategory->getName().' saved ('.$products.')', Zend_Log::DEBUG, 'gareth.log');
			}
		}
		
		// Now save root category
		/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
		$constants= Mage::helper('gareth_naturescupboard2/constants');
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		/* @var Mage_Core_Model_Store $store */
		$store = $lookup->getStore($constants->getNCStoreViewCode());
		/* @var Mage_Catalog_Model_Category $rootCategory */
		$rootCategory = $lookup->getRootCategory($store);
		
		$rootCategoryProductsPosition = $categoryProducts[$rootCategory->getId()];
		$rootCategory->setPostedProducts($rootCategoryProductsPosition);
		$rootCategory->save();
		
		$products = array_keys($rootCategoryProductsPosition);
		$products = implode(',',$products);
		Mage::log('Category '.$rootCategory->getName().' saved ('.$products.')', Zend_Log::DEBUG, 'gareth.log');
				
		// indexes are all intact at this point
	}
	
	/**
	 * Function called when the catalog_product_save_after event is fired.
	 * Observer configured in config.xml.
	 * 
	 * The changes made by the admin will be in the product object and not in
	 * the DB. So getAttributeRawData() will return the old value not the new
	 * value.
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public function setCategoriesOnProduct($observer)
	{
		/* @var Mage_Catalog_Model_Product $product */
		$product = $observer->getEvent()->getProduct();
		
		Mage::log('setCategoriesOnProduct called on '.$product->getName(), Zend_Log::NOTICE, 'gareth.log');
		
		/* @var array $mappings attribute_code=>category_object */
		$mappings = $this->getAttributeCodeToCategoryMap();
		
		/* @var array $categoryProducts category_id => array(product_id => position) */
		$categoryProducts = $this->getCategoryProducts($mappings);
		
		/* The attributes and their values for this product */
		/* @var array $attributesValues attribute_code=>attribute_value */
		$attributesValues = $this->getMappedAttributesAndValuesFromProduct($mappings, $product);
				
		$categoryProducts = $this->setProductCategories($product->getId(), $attributesValues, $mappings, $categoryProducts);
		
		$this->saveMappedCategories($mappings, $categoryProducts);
	} 
	
	/**
	 * Function called when the catalog_product_import_finish_before event is
	 * fired. Observer configured in config.xml.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function setCategoriesOnMultipleProducts($observer)
	{
		/* @var array $importedProductIds */
		$importedProductIds = $observer->getAdapter()->getAffectedEntityIds();
		
		if (!empty($importedProductIds))
		{
			Mage::log('setCategoriesOnMultipleProducts called with ['.implode(',',$importedProductIds).']', Zend_Log::NOTICE, 'gareth.log');

			/* @var array $mappings attribute_code=>category_object */
			$mappings = $this->getAttributeCodeToCategoryMap();
			
			/* @var array $categoryProducts category_id => array(product_id => position) */
			$categoryProducts = $this->getCategoryProducts($mappings);
			
			foreach ($importedProductIds as $productId)
			{
				Mage::log('  Product '.$productId.':', Zend_Log::NOTICE, 'gareth.log');
				
				/* The attributes and their values for this product */
				/* @var array $attributesValues attribute_code=>attribute_value */
				$attributesValues = $this->getMappedAttributesAndValuesFromDatabase($mappings, intval($productId));
				
				$categoryProducts = $this->setProductCategories($productId, $attributesValues, $mappings, $categoryProducts);
			}
			
			$this->saveMappedCategories($mappings, $categoryProducts);
		}
		else 
		{
			Mage::log('setCategoriesOnMultipleProducts called with no affected products', Zend_Log::NOTICE, 'gareth.log');
		}
	}
}
