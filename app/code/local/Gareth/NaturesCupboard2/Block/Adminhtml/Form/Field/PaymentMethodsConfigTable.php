<?php
/**
 * Natures Cupboard 2 Magento extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is copyright Gareth Ladd 2020. Not for public dissemination
 * nor use.
 *
 * DISCLAIMER
 *
 * This program is private software. It comes without any warranty, to
 * the extent permitted by applicable law. You may not copy, modify nor
 * distribute it. The author takes no responsibility for any consequences of
 * unauthorised usage of this file or any part thereof.
 */

class Gareth_NaturesCupboard2_Block_Adminhtml_Form_Field_PaymentMethodsConfigTable
extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	/**
	 * Payment Method renderer block cache
	 * 
	 * @var Gareth_NaturesCupboard2_Block_Adminhtml_Form_Field_PaymentMethodsSelect $_groupRenderer
	 */
	protected $_methodRenderer;

	/**
	 * Customer groups cache
	 *
	 * @var array $_customerGroups array of customer_group_id => customer_group_label
	 */
	protected $_customerGroups;
	
	/**
	 * Cache of renderers for the column value selects. Each value is a block
	 * that will render a gareth_naturescupboard2/adminhtml_form_field_allowDisallowSelect
	 * HTML field.
	 * @var array col_name => renderer
	 */
	protected $_allowRenderers = array();
	
	/**
	 * Return a gareth_naturescupboard2/adminhtml_form_field_allowDisallowSelect
	 * block renderer for the specified column name.
	 * 
	 * @param string|int $name
	 */
	protected function _getAllowRenderer($name)
	{
		if (!array_key_exists($name, $this->_allowRenderers))
		{
			$renderer = $this->getLayout()->createBlock(
					'gareth_naturescupboard2/adminhtml_form_field_allowDisallowSelect', '',
					array('is_render_to_js_template' => true)
					);
			$renderer->setExtraParams('style="width:13ex"');
			$this->_allowRenderers[$name] = $renderer;
		}
		return $this->_allowRenderers[$name];
	}
	
	/**
	 * Retrieve payment method column renderer
	 *
	 * @return Gareth_NaturesCupboard2_Block_Adminhtml_Form_Field_PaymentMethodsSelect
	 */
	protected function _getMethodRenderer()
	{
		if (!$this->_methodRenderer) {
			$this->_methodRenderer = $this->getLayout()->createBlock(
					'gareth_naturescupboard2/adminhtml_form_field_paymentMethodsSelect', '',
					array('is_render_to_js_template' => true)
					);
			/*$this->$_methodRenderer->setClass('customer_group_select');*/
			$this->_methodRenderer->setExtraParams('style="width:15ex"');
		}
		return $this->_methodRenderer;
	}

	/**
	 * Retrieve all customer groups
	 *
	 * @return array
	 */
	protected function _getCustomerGroups()
	{
		if (is_null($this->_customerGroups)) {
			$this->_customerGroups = array();
			$collection = Mage::getModel('customer/group')->getCollection();
			foreach ($collection as $item)
			{
				/* @var $item Mage_Customer_Model_Group */
				$this->_customerGroups[$item->getId()] = $item->getCustomerGroupCode();
			}
		}
		return $this->_customerGroups;
	}
	
	/**
	 * Prepare existing row data object. Called for each row in the array stored
	 * in the config db table. Adds the selected HTML attribute to the select
	 * option represented in the database.
	 *
	 * @param Varien_Object $row simple data transfer object with $row[col_name]=>col_value
	 */
	protected function _prepareArrayRow(Varien_Object $row)
	{
		parent::_prepareArrayRow($row);
		
		/**
		 * this is required for the option stored in the config db table to be
		 * the selected one in the dropdown. Goodness knows how it works!!
		 */
		$selectedOption = $row->getData('method');
		$method_option_reference = 'option_extra_attr_' . $this->_getMethodRenderer()->calcOptionHash($selectedOption);
		$row->setData($method_option_reference,	'selected="selected"');
		
		foreach ($this->_allowRenderers as $col_name => $renderer)
		{
			$selectedOption = $row->getData($col_name);
			$method_option_reference = 'option_extra_attr_' . $renderer->calcOptionHash($selectedOption);
			$row->setData($method_option_reference,	'selected="selected"');
			
		}
		
	}
	
	
	/**
	 * Prepare to render. Add custom config field columns, set template, add values.
	 */
	protected function _prepareToRender()
	{
		parent::_prepareToRender();
		
		/** @var ArchApps_CustomAdminConfig_Helper_Data $helper */
		$helper = Mage::helper('gareth_naturescupboard2/data');
		
		$this->addColumn('method', array(
				'style' => 'width:15em',
				'label' => $helper->__('Payment Method'),
				'renderer' => $this->_getMethodRenderer(),
		));
		
		$customerGroups = $this->_getCustomerGroups();
		foreach ($customerGroups as $group_id => $group_label)
		{
			$this->addColumn($group_id, array(
					'style' => 'width:7ex',
					'label' => $helper->__($group_label),
					'renderer' => $this->_getAllowRenderer($group_id),
			));
		}
		
		$this->_addAfter = false;
	}
}