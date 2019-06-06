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

/* @var $this Gareth_NaturesCupboard2_Model_Resource_Setup */
/* @var $installer Gareth_NaturesCupboard2_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

Mage::log('Running data-upgrade-1.0.0-1.0.1 script', Zend_Log::NOTICE, 'gareth.log');

/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
$lookup= Mage::helper('gareth_naturescupboard2/lookup');

$storeName = 'Natures Cupboard Store View';
$store = $lookup->getStore($storeName);

/** @var Gareth_NaturesCupboard2_Helper_Product $productHelper */
$productHelper = Mage::helper('gareth_naturescupboard2/product');

/**
 * The sql upgrade script has added a new "category_id" column to the mapping 
 * table. Do a one-time loop through all mappings and set this value based
 * upon the url-key column.
 */
Mage::log('Updating all attribute-to-category-mappings', Zend_Log::NOTICE, 'gareth.log');
$allMappings = Mage::getModel('gareth_naturescupboard2/attribtocategorymapping')->getCollection();
/* @var Gareth_NaturesCupboard2_Model_AttribToCategoryMapping $mapping */
foreach ($allMappings as $mapping)
{
	$urlKey = $mapping->getCategoryUrlKey();
	$category = $lookup->findCategoryByUrlKey($store, $urlKey);
	$categoryId = $category->getId();
	$mapping->setCategoryId($categoryId);
	
	$mapping->save();
	Mage::log('Set mapping '.$urlKey.' to category '.$category->getName().'(id '.$categoryId.')', Zend_Log::NOTICE, 'gareth.log');
}

/**
 * Because the previous version did not auto-remove products from categories,
 * do a one-time pass over all products and correctly set categories.
 */
Mage::log('Re-setting categories of all products', Zend_Log::NOTICE, 'gareth.log');$allProducts = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
/* @var Mage_Catalog_Model_Product $product */
foreach ($allProducts as $product)
{
	$productHelper->setCategoriesFromAttributes($product);
	// TODO DOES this trigger catalog_product_save_before observer even in an
	// install/upgrade script? Do we need the previous line?
	$product->save();
	$categoryIds = implode(',',$product->getCategoryIds());
	Mage::log('Set categories on product: '.$product->getName().' to '.$categoryIds, Zend_Log::DEBUG, 'gareth.log');
}
Mage::log('data-upgrade-1.0.0-1.0.1 script finished', Zend_Log::NOTICE, 'gareth.log');


$installer->endSetup();