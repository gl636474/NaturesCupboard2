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
 * values of particular attributes of that product. Also locks the margin fields
 * when the admin is editing a product because anhy value(s) entered would be
 * immediately overwritten by these methods anyway.
 * <p>
 * Needs to be configured to listen for the following events:
 * <ul>
 * <li>catalog_product_save_before</li>
 * <li>catalog_product_import_finish_before</li>
 * <li>catalog_product_edit_action</li>
 * </ul>
 * </p>
 * Requires the following in config.xml:
 * <pre>
  	&lt;adminhtml&gt;
        &lt;events&gt;
            &lt;catalog_product_save_before&gt;
                &lt;observers&gt;
                    &lt;Gareth_NaturesCupboard2_Product_Save_Margin_Calculator_Observer&gt;
                      &lt;type&gt;singleton&lt;/type&gt;
                        &lt;class&gt;Gareth_NaturesCupboard2_Model_Observer_Margins&lt;/class&gt;
                        &lt;method&gt;calculateMarginsOnProduct&lt;/method&gt;
                    &lt;/Gareth_NaturesCupboard2_Product_Save_Margin_Calculator_Observer&gt;
                &lt;/observers&gt;
            &lt;/catalog_product_save_before&gt;
            &lt;catalog_product_import_finish_before&gt;
                &lt;observers&gt;
                    &lt;Gareth_NaturesCupboard2_Product_Save_Margin_Calculator_Observer&gt;
                      &lt;type&gt;singleton&lt;/type&gt;
                        &lt;class&gt;Gareth_NaturesCupboard2_Model_Observer_Margins&lt;/class&gt;
                        &lt;method&gt;calculateMarginsOnMultipleProducts&lt;/method&gt;
                    &lt;/Gareth_NaturesCupboard2_Product_Save_Margin_Calculator_Observer&gt;
                &lt;/observers&gt;
            &lt;/catalog_product_import_finish_before&gt;
            &lt;catalog_product_edit_action&gt;
                &lt;observers&gt;
                   &lt;Gareth_NaturesCupboard2_Model_Product_Margin_Lock_Observer&gt;
                      &lt;type&gt;singleton&lt;/type&gt;
                        &lt;class&gt;Gareth_NaturesCupboard2_Model_Observer_Margins&lt;/class&gt;
                        &lt;method&gt;lockMarginsOnProduct&lt;/method&gt;
                    &lt;/Gareth_NaturesCupboard2_Model_Product_Margin_Lock_Observer&gt;
                &lt;/observers&gt;
            &lt;/catalog_product_edit_action&gt;
        &lt;/events&gt;
    &lt;/adminhtml&gt;

 * </pre>
 * @author gareth
 */
class Gareth_NaturesCupboard2_Model_Observer_Margins extends Varien_Object
{
	/**
	 * Calculates the margin in pounds and percent given the 
	 * 
	 */
	protected function calculateProductMargins($price, $cost)
	{		
		if (is_numeric($price) && $price > 0 && is_numeric($cost) && $cost > 0)
		{
			$margin_pounds = $price - $cost;
			$margin_percent = round(($margin_pounds / $price) * 100, 1, PHP_ROUND_HALF_DOWN);
			Mage::log('     Margin: £'.$margin_pounds.' ('.$margin_percent.'%)', Zend_Log::DEBUG, 'gareth.log');
		}
		else
		{
			$margin_pounds = null;
			$margin_percent = null;
			Mage::log('     Margin null because empty price ('.$price.') or cost ('.$cost.')', Zend_Log::DEBUG, 'gareth.log');
		}
		return array($margin_pounds, $margin_percent);
	}
	
	/**
	 * Function called when the catalog_product_save_before event is fired.
	 * Observer configured in config.xml.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function calculateMarginsOnProduct($observer)
	{
		/* @var Mage_Catalog_Model_Product $product */
		$product = $observer->getEvent()->getProduct();
		$name = $product->getName();
		Mage::log('calculateMarginsOnProduct called on '.$name, Zend_Log::DEBUG, 'gareth.log');
		
		$price = $product->getPrice();
		$cost = $product->getCost();
		
		list($pounds, $percent) = $this->calculateProductMargins($price, $cost);
		$product->setMarginPounds($pounds);
		$product->setMarginPercent($percent);
		
		// save() will be called later by the caller
		Mage::log('     Values set', Zend_Log::DEBUG, 'gareth.log');
	}

	/**
	 * Function called when the catalog_product_import_finish_before event is
	 * fired. Observer configured in config.xml.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function calculateMarginsOnMultipleProducts($observer)
	{
		/* @var array $importedProductIds */
		$importedProductIds = $observer->getAdapter()->getAffectedEntityIds();
		Mage::log('calculateMarginsOnMultipleProducts called with ['.implode(',',$importedProductIds).']', Zend_Log::NOTICE, 'gareth.log');
		
		if (!empty($importedProductIds))
		{
			/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
			$constants = Mage::helper('gareth_naturescupboard2/constants');
			/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
			$lookup = Mage::helper('gareth_naturescupboard2/lookup');
			/* @var Mage_Core_Model_Store $store */
			$store = $lookup->getStore($constants->getNCStoreViewCode());
			$storeId = $store->getId();
			
			foreach ($importedProductIds as $productId)
			{
				/* @var Mage_Catalog_Model_Resource_Product $resource */
				$resource = Mage::getResourceModel('catalog/product');
				$attributeCodes = Array('name', 'price', 'cost');
				$attributesValues = $resource->getAttributeRawValue($productId, $attributeCodes, $storeId);
				
				$name = array_key_exists('name', $attributesValues) ? $attributesValues['name'] : null;
				$price = array_key_exists('price', $attributesValues) ? $attributesValues['price'] : null;
				$cost = array_key_exists('cost', $attributesValues) ? $attributesValues['cost'] : null;
				
				Mage::log('  '.$name.':', Zend_Log::NOTICE, 'gareth.log');
				
				list($pounds, $percent) = $this->calculateProductMargins($price, $cost);
				
				$attributesValues = array(
						'margin_pounds' => $pounds, 
						'margin_percent' => $percent);
				
				/* @var Mage_Catalog_Model_Resource_Product_Action $action */
				$action = Mage::getModel('catalog/resource_product_action');
				$action->updateAttributes(array($productId), $attributesValues, $storeId);

				Mage::log('     Updated', Zend_Log::NOTICE, 'gareth.log');
			}
			// indexes are all intact at this point
		}
		else
		{
			Mage::log('calculateMarginsOnMultipleProducts called with no affected products', Zend_Log::NOTICE, 'gareth.log');
		}
	}

	/**
	 * Function called the catalog_product_edit_action
	 * observer configured in config.xml
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function lockMarginsOnProduct($observer)
	{
		/* @var Mage_Catalog_Model_Product $product */
		$product = $observer->getEvent()->getProduct();
		
		Mage::log('lockMarginsOnProduct called on '.$product->getName(), Zend_Log::DEBUG, 'gareth.log');
		
		$product->lockAttribute('margin_pounds');
		$product->lockAttribute('margin_percent');
	}
	
}
