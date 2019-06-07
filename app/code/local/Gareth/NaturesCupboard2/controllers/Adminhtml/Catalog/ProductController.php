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
}
