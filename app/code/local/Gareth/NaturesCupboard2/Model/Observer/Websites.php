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
 * Observer to set the website of a product to Natures Cupboard if the product
 * does not already have a website.
 * <p>
 * Needs to be configured to listen for the following events:
 * <ul>
 * <li>catalog_product_save_before</li>
 * <li>catalog_product_import_finish_before</li>
 * </ul>
 * </p>
 * Requires the following in config.xml:
 * <pre>
  	&lt;adminhtml&gt;
        &lt;events&gt;
            &lt;catalog_product_save_before&gt;
                &lt;observers&gt;
                    &lt;Gareth_NaturesCupboard2_Product_Save_Website_Checker_Observer&gt;
                      &lt;type&gt;singleton&lt;/type&gt;
                        &lt;class&gt;Gareth_NaturesCupboard2_Model_Observer_Margins&lt;/class&gt;
                        &lt;method&gt;calculateMarginsOnProduct&lt;/method&gt;
                    &lt;/Gareth_NaturesCupboard2_Product_Save_Website_Checker_Observer&gt;
                &lt;/observers&gt;
            &lt;/catalog_product_save_before&gt;
            &lt;catalog_product_import_finish_before&gt;
                &lt;observers&gt;
                    &lt;Gareth_NaturesCupboard2_Product_Save_Website_Checker_Observer&gt;
                      &lt;type&gt;singleton&lt;/type&gt;
                        &lt;class&gt;Gareth_NaturesCupboard2_Model_Observer_Margins&lt;/class&gt;
                        &lt;method&gt;calculateMarginsOnMultipleProducts&lt;/method&gt;
                    &lt;/Gareth_NaturesCupboard2_Product_Save_Website_Checker_Observer&gt;
                &lt;/observers&gt;
            &lt;/catalog_product_import_finish_before&gt;
        &lt;/events&gt;
    &lt;/adminhtml&gt;

 * </pre>
 * @author gareth
 */
class Gareth_NaturesCupboard2_Model_Observer_Websites extends Varien_Object
{
	/**
	 * Function called when the catalog_product_save_before event is fired.
	 * Observer configured in config.xml.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function checkWebsitesOnProduct($observer)
	{
		/* @var Mage_Catalog_Model_Product $product */
		$product = $observer->getEvent()->getProduct();
		$name = $product->getName();
		Mage::log('checkWebsitesOnProduct called on '.$name, Zend_Log::DEBUG, 'gareth.log');
		
		if (count($product->getWebsiteIds()) == 0)
		{
			/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
			$constants = Mage::helper('gareth_naturescupboard2/constants');
			/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
			$lookup = Mage::helper('gareth_naturescupboard2/lookup');
			/* @var Mage_Core_Model_Store $store */
			$store = $lookup->getStore($constants->getNCStoreViewCode());
			
			$websiteId = $store->getWebsiteId();
			$product->setWebsiteIds(array($websiteId));
			Mage::log('    Website set to '.$websiteId, Zend_Log::DEBUG, 'gareth.log');
		}
		else 
		{
			Mage::log('    Website(s) already set', Zend_Log::DEBUG, 'gareth.log');
		}
	}

	/**
	 * Function called when the catalog_product_import_finish_before event is
	 * fired. Observer configured in config.xml.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function checkWebsitesOnMultipleProducts($observer)
	{
		/* @var array $importedProductIds */
		$importedProductIds = $observer->getAdapter()->getAffectedEntityIds();
		Mage::log('checkWebsitesOnMultipleProducts called with ['.implode(',',$importedProductIds).']', Zend_Log::NOTICE, 'gareth.log');
		
		if (!empty($importedProductIds))
		{
			/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
			$constants = Mage::helper('gareth_naturescupboard2/constants');
			/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
			$lookup = Mage::helper('gareth_naturescupboard2/lookup');
			/* @var Mage_Core_Model_Store $store */
			$store = $lookup->getStore($constants->getNCStoreViewCode());
			
			$websiteId = $store->getWebsiteId();

			foreach ($importedProductIds as $productId)
			{
				$this->setProductWebsiteId($productId, $websiteId);
			}
		}
		else
		{
			Mage::log('checkWebsitesOnMultipleProducts called with no affected products', Zend_Log::NOTICE, 'gareth.log');
		}
	}
	
	/**
	 * Uses a direct DB write to add the supplied product to the supplied
	 * website. This function will check first that the product is not already
	 * on the website, to avoid a DB constraint violation exception.
	 * 
	 * @param integer $productId
	 * @param integer $websiteId
	 */
	protected function setProductWebsiteId($productId, $websiteId)
	{
		/* @var Mage_Core_Model_Resource $core_resource */
		$core_resource = Mage::getSingleton('core/resource');
		/* @var Magento_Db_Adapter_Pdo_Mysql $read_adapter */
		$read_adapter = $core_resource->getConnection('core_read');
		/* @var Magento_Db_Adapter_Pdo_Mysql $write_adapter */
		$write_adapter = $core_resource->getConnection('core_write');
		/* @var Mage_Catalog_Model_Resource_Product $product_resource */
		$product_resource = Mage::getResourceModel('catalog/product');
		
		$websitesTable = $product_resource->getTable('catalog/product_website');
		

		$results = $read_adapter->fetchRow("select * from $websitesTable where product_id = ? and website_id = ?",
				array($productId, $websiteId));
		
		$alreadyPresent = !empty($results);
		
		if(!$alreadyPresent)
		{
			$data = array(
				'product_id' => (int)$productId,
				'website_id' => (int)$websiteId
			);
			$write_adapter->insert($websitesTable, $data);
			Mage::log("Added product $productId to website $websiteId", Zend_Log::INFO, 'gareth.log');
		}
		else 
		{
			Mage::log("Product $productId already in website $websiteId", Zend_Log::DEBUG, 'gareth.log');
		}
	}
}
