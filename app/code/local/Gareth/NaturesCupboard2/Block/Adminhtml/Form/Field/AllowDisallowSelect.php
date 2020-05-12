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
 * HTML select element block with 2 options: allow and disallow
 *
 */
class Gareth_NaturesCupboard2_Block_Adminhtml_Form_Field_AllowDisallowSelect extends Mage_Core_Block_Html_Select
{
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
		$allowLabel = Mage::helper('gareth_naturescupboard2/data')->__('Allow');
		$this->addOption(1, $allowLabel);
		
		$disallowLabel = Mage::helper('gareth_naturescupboard2/data')->__('Disallow');
		$this->addOption(0, $disallowLabel);

		return parent::_toHtml();
	}
}
