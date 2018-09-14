<?php
/**
 * Note this must be called Gareth_NaturesCupboard2_Model_Observer if
 * it is in the Model directory. To call it 
 * Gareth_NaturesCupboard2_Model_Product_Observer we must put it in
 * a Product subdirectory for the Magento autoload facility to find it.
 * 
 * @author gareth
 *
 */
class Gareth_NaturesCupboard2_Model_Observer extends Varien_Object
{
	/**
	 * The group name under which our attributes are listed within the attribute
	 * sets (group name seen in admin pages only)
	 *
	 * @var string
	 */
	private static $_attributeSetGroupName = 'NC Product Info';
	
	/**
	 * A regular expression for the name of the store to which to add
	 * categories, attributes, etc..
	 * @var string
	 */
	private static $_theStoreRegex = "/nature.?s.*cupboard/i";
	
	/**
	 * Returns an array of attribute code to category URL key which
	 * represents the categories to which products should be added to if the
	 * product has the specified attribute set to true.
	 */
	private function getAttributeCodeToCategoryUrlKeyMapping()
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
			Mage::log($attributeCode.'=>'.$categoryUrlKey, null, 'gareth.log');
		}
		return $mappings;
	}
	
	/**
	 * Function call ed the observer configured in config.xml
	 * 
	 * @param unknown $observer
	 */
	public function autoSetCategories($observer)
	{
		/* @var Mage_Catalog_Model_Product $product */
		$product = $observer->getEvent()->getProduct();
		
		$productName = $product->getName();
		Mage::log('Called autoSetCategories on '.$productName, null, 'gareth.log');

		$mappings = $this->getAttributeCodeToCategoryUrlKeyMapping();
		
		$categoryIds = array();
		$product_attributes = $product->getAttributes();//self::$_attributeSetGroupName, false);
		Mage::log($productName.' has '.count($product_attributes).' attributes', null, 'gareth.log');
		/* @var Mage_Catalog_Model_Resource_Eav_Attribute $thisAttribute */
		foreach ($product_attributes as $thisAttribute)
		{
			$thisAttributeCode = $thisAttribute->getAttributeCode();
			if (array_key_exists($thisAttributeCode, $mappings) && $product->getData($thisAttributeCode))
			{
				$categoryUrlKeyToAddTo = $mappings[$thisAttributeCode];
				
				/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
				$lookup= Mage::helper('gareth_naturescupboard2/lookup');
				/** @var Mage_Catalog_Model_Category $categoryToAddTo */
				$categoryToAddTo = $lookup->findCategoryByUrlKey(self::$_theStoreRegex, $categoryUrlKeyToAddTo);
				if (!is_null($categoryToAddTo))
				{
					Mage::log($thisAttributeCode.' maps to category '.$categoryUrlKeyToAddTo.'('.$categoryId.')', null, 'gareth.log');
					$categoryId = $categoryToAddTo->getId();
					$categoryIds[] = $categoryId;
					$parentCategoryIds = $categoryToAddTo->getParentIds();
					$categoryIds = array_merge($categoryIds, $parentCategoryIds);
				}
				else
				{
					Mage::log($thisAttributeCode.' maps to non existent category urlkey:'.$categoryUrlKeyToAddTo, null, 'gareth.log');
				}
			}
		}

		$categoryIds = array_unique($categoryIds);
		$product->setCategoryIds($categoryIds);
		Mage::log('Product '.$productName.' re-assigned categories: '.implode(',',$categoryIds), null, 'gareth.log');
		
		
		// TODO flush cache if made any changes ?
	
	} 
	
	
}
