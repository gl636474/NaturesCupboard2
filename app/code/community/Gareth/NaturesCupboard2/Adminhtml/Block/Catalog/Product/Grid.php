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

include_once('Mage/Adminhtnl/Bock/Catalog/Product/Grid.php');

/**
 * Override of Mage_Adminhtml_Block_Catalog_Product_Grid in 
 * Mage/Adminhtnl/Bock/Catalog/Product/Grid.php to add the export option to
 * the mass action dropdown. All code is a direct copy from Magento core except
 * for the addition highlighted by comments.
 * 
 * @see https://stackoverflow.com/questions/3468961/export-products-to-csv-from-the-admin-product-grid
 */
class Gareth_NaturesCupboard2_Adminhtml_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{	
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('product');
		
		$this->getMassactionBlock()->addItem('delete', array(
				'label'=> Mage::helper('catalog')->__('Delete'),
				'url'  => $this->getUrl('*/*/massDelete'),
				'confirm' => Mage::helper('catalog')->__('Are you sure?')
		));
		
		$statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();
		
		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
				'label'=> Mage::helper('catalog')->__('Change status'),
				'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
				'additional' => array(
						'visibility' => array(
								'name' => 'status',
								'type' => 'select',
								'class' => 'required-entry',
								'label' => Mage::helper('catalog')->__('Status'),
								'values' => $statuses
						)
				)
		));
		
		if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
			$this->getMassactionBlock()->addItem('attributes', array(
					'label' => Mage::helper('catalog')->__('Update Attributes'),
					'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true))
			));
		}
		
		// Start of section added by Gareth
		$this->getMassactionBlock()->addItem('export', array(
				'label'=> Mage::helper('catalog')->__('Export to CSV'),
				'url'  => $this->getUrl('*/*/massExport')
		));
		// End of section added by Gareth
		
		
		Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
		return $this;
	}
}
