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

/**
 * HTML select element block with payment method options
 *
 */
class Gareth_NaturesCupboard2_Block_Adminhtml_Form_Field_PaymentMethodsSelect extends Mage_Core_Block_Html_Select
{
	/** @var array Payment methods cache */
	private $_paymentMethods = null;
	
	/**
	 * Retrieve allowed payment methods
	 *
	 * @return array
	 */
	protected function _getPaymentMethods()
	{
		if (is_null($this->_paymentMethods))
		{
			$this->_paymentMethods = array();
			$enabledMethods = array();
			$disabledMethods = array();
			$activeLabel = Mage::helper('gareth_naturescupboard2/data')->__('Active');
			
			/* @var Mage_Payment_Model_Config $methodModel */
			$methodModel = Mage::getModel('payment/config');
			/* @var array $allActivePaymentMethods */
			$allPaymentMethods = $methodModel->getAllMethods();
			/* @var Mage_Payment_Model_Method_Abstract $method */
			foreach($allPaymentMethods as $method)
			{
				$methodTitle = $method->getTitle() ? $method->getTitle() : $method->getCode();
				if ($method->isAvailable())
				{
					$enabledMethods[$method->getCode()] = $methodTitle.' ('.$activeLabel.')';
				}
				else
				{
					// save to add at the end
					$disabledMethods[$method->getCode()] = $methodTitle;
				}
			}
			$this->_paymentMethods = array_merge($enabledMethods, $disabledMethods);
		}
		return $this->_paymentMethods;
	}
	
	public function setInputName($value)
	{
		return $this->setName($value);
	}
	
	/**
	 * Render block HTML
	 *
	 * @return string
	 */
	public function _toHtml()
	{
		if (!$this->getOptions())
		{
			foreach ($this->_getPaymentMethods() as $methodId => $methodTitle)
			{
				$this->addOption($methodId, addslashes($methodTitle));
			}
		}
		return parent::_toHtml();
	}
}
