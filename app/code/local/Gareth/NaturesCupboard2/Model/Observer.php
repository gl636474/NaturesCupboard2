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
	 * Function called the catalog_product_save_before
	 * observer configured in config.xml
	 * 
	 */
	public function setCategoriesOnProduct($observer)
	{
		/* @var Mage_Catalog_Model_Product $product */
		$product = $observer->getEvent()->getProduct();
		
		Mage::log('setCategoriesOnProduct called on '.$product->getName(), null, 'gareth.log');
		
		/** @var Gareth_NaturesCupboard2_Helper_Product $productHelper */
		$productHelper = Mage::helper('gareth_naturescupboard2/product');
		$productHelper->setCategoriesFromAttributes($product);
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
