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

include_once("Mage/Adminhtml/controllers/Catalog/ProductController.php");

/**
 * Catalog product controller. Overide of 
 * Mage/Adminhtml/controlers/Catalog/ProductController to add a handler 
 * for the export mass action.
 *
 * @see https://magento.stackexchange.com/questions/87382/rewriting-app-code-core-mage-adminhtml-contollers-catalog-productcontroller-php
 * @see https://magento.stackexchange.com/questions/87649/how-to-override-the-product-controller-in-admin-panel
 * @see https://stackoverflow.com/questions/3468961/export-products-to-csv-from-the-admin-product-grid
 * @see https://magento.stackexchange.com/questions/208163/custom-admin-route-giving-404
 * @author gareth
 */
class Gareth_NaturesCupboard2_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
	/**
	 * The product atributes to export - in order. Named by their 
	 * lowercase_underscore database name (as passed to getData()).
	 * 
	 * @var array
	 */
	protected static $fieldsToExport = array(
			'status',
			'price',
			'name',
			'cost',
			'margin_pounds',
			'margin_percent',
			'tax_class_id',
			'url_key',
			'visibility',
			
			'is_food',
			'is_personal_care',
			'is_baby',
			'is_household',
			
			'is_organic',
			'is_eco_friendly',
			'is_vegan',
			'is_vegetarian',
			
			'is_gluten_free',
			'is_dairy_free',
			'is_no_added_sugar',
			'is_raw',
			'is_preservative_free',
			'is_gmo_free',
			
			'ingredients',
			
			'weight',
			'package_height',
			'package_width',
			'package_depth',
			
			'short_description',
			'description',
	);
	
	/**
	 * Export product(s) mass action.
	 * 
	 * @author Gareth
	 */
	public function massExportAction()
	{
		$productIds = $this->getRequest()->getParam('product');
		if (!is_array($productIds)) {
			$this->_getSession()->addError($this->__('Please select product(s).'));
			$this->_redirect('*/*/index');
		}
		else
		{
			try
			{
				//write headers to the csv file
				$content = '"sku","qty","is_in_stock"';
				foreach (self::$fieldsToExport as $field)
				{
					$content .= ',"'.$field.'"';
				}
				$content .= ',"_attribute_set","_type"'."\n";
				
				/** @var Mage_Eav_Model_Config $eavConfig */
				$eavConfig = Mage::getModel('eav/config');
				
				//write data to the csv file
				foreach ($productIds as $productId)
				{
					/** @var Mage_Catalog_Model_Product $product */
					$product = Mage::getSingleton('catalog/product')->load($productId);
					/** @var Mage_Eav_Model_Entity_Attribute_Set $attributeSetModel */
					$attributeSet = Mage::getModel("eav/entity_attribute_set")->load($product->getAttributeSetId());
					/** @var Mage_CatalogInventory_Model_Stock_Item $stock_item */
					$stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
					
					$attributeCodes = $eavConfig->getEntityAttributeCodes(
						Mage_Catalog_Model_Product::ENTITY, $product);
					
					$content .= '"'.$product->getSku().'"';
					$content .= ',"'.$stock_item->getQty().'"';
					$content .= ',"'.$stock_item->getIsInStock().'"';
					
					foreach (self::$fieldsToExport as $field)
					{
						if (in_array($field, $attributeCodes))
						{
							$data = $product->getData($field);
							$data = (string)$data;
							$data = str_replace('"', '""', $data);
							$content .= ',"'.$data.'"';
						}
						else
						{
							$content .= ',';
						}
					}
					
					$content .= ',"'.$attributeSet->getAttributeSetName().'"';
					$content .= ',"'.$product->getTypeId().'"';
					$content .= "\n";
				}
			}
			catch (Exception $e)
			{
				$this->_getSession()->addError($e->getMessage());
				$this->_redirect('*/*/index');
			}
			$this->_prepareDownloadResponse('natures_cupboard_products.csv', $content, 'text/csv');
		}
	}
	
	/**
	 * Export cost sheet for product(s) action. This is a spreadsheet for manual
	 * orders, containing product name, price and empty columns for quantity to
	 * order and total price.
	 *
	 * @author Gareth
	 */
	public function pricingSheetAction()
	{
		$productIds = $this->getRequest()->getParam('product');
		if (!is_array($productIds)) {
			$this->_getSession()->addError($this->__('Please select product(s).'));
			$this->_redirect('*/*/index');
		}
		else
		{
			try
			{
				//write headers to the csv file
				$content = '"Product","Price per unit","Quantity","Total Price"'."\n";

				//write data to the csv file
				foreach ($productIds as $productId)
				{
					/** @var Mage_Catalog_Model_Product $product */
					$product = Mage::getSingleton('catalog/product')->load($productId);
					
					$content .= '"'.$product->getName().'",';
					$content .= '"'.$product->getPrice().'",';
					$content .= '"",""'; //empty cols for qantity and total price
					$content .= "\n";
				}
				
				$content .= '"","","",""'."\n"; // blank line
				$content .= '"Grand Total","--->","--->",""'."\n"; // footer
			}
			catch (Exception $e)
			{
				$this->_getSession()->addError($e->getMessage());
				$this->_redirect('*/*/index');
			}
			
			$date = date('d_m_Y');
			$filename = 'natures_cupboard_prices_'.$date.'.csv';
			$this->_prepareDownloadResponse($filename, $content, 'text/csv');
		}
	}
	
	/**
	 * Export cost and margins sheet for product(s) action. This is a
	 * spreadsheet for store owner to see product name, selling price, cost
	 * price and margin (in £s and %) for each product.
	 *
	 * @author Gareth
	 */
	public function marginsSheetAction()
	{
		$productIds = $this->getRequest()->getParam('product');
		if (!is_array($productIds)) {
			$this->_getSession()->addError($this->__('Please select product(s).'));
			$this->_redirect('*/*/index');
		}
		else
		{
			try
			{
				/** @var Gareth_NaturesCupboard2_Helper_Constants $constants */
				$constants = Mage::helper('gareth_naturescupboard2/constants');
				/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
				$lookup = Mage::helper('gareth_naturescupboard2/lookup');
				
				$storeCode = $constants->getNCStoreViewCode();
				$store = $lookup->getStore($storeCode);
				
				//write headers to the csv file
				$content = '"SKU","Product","Price per unit","Cost Price","Margin (£)","Margin (%)"'."\n";

				//write data to the csv file
				foreach ($productIds as $productId)
				{
					/** @var Mage_Catalog_Model_Resource_Product $resource */
					$resource = Mage::getSingleton('catalog/product')->getResource();
					
					$sku = $resource->getAttributeRawValue($productId, 'sku', $store);
					$name = $resource->getAttributeRawValue($productId, 'name', $store);
					
					$price = $resource->getAttributeRawValue($productId, 'price', $store);
					$cost = $resource->getAttributeRawValue($productId, 'cost', $store);
					$margin_pounds = $resource->getAttributeRawValue($productId, 'margin_pounds', $store);
					$margin_percent = $resource->getAttributeRawValue($productId, 'margin_percent', $store);
					
					$price = is_numeric($price) ? round($price,2) : "";
					$cost = is_numeric($cost) ? round($cost,2) : "";
					$margin_pounds = is_numeric($margin_pounds) ? round($margin_pounds,2) : "";
					$margin_percent = is_numeric($margin_percent) ? round($margin_percent,1) : "";
					
					// NB: false & null are concatonated as empty string
					$content .= '"'.$sku.'",';
					$content .= '"'.$name.'",';
					$content .= '"'.$price.'",';
					$content .= '"'.$cost.'",';
					$content .= '"'.$margin_pounds.'",';
					$content .= '"'.$margin_percent.'"';
					$content .= "\n";
				}
			}
			catch (Exception $e)
			{
				$this->_getSession()->addError($e->getMessage());
				$this->_redirect('*/*/index');
			}
			
			$date = date('d_m_Y');
			$filename = 'natures_cupboard_margins_'.$date.'.csv';
			$this->_prepareDownloadResponse($filename, $content, 'text/csv');
		}
	}
}
