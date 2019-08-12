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
 * Helper functions to provide NaturesCupboard specific constants (e.g. Store
 * code.
 * 
 * @author gareth
 */
class Gareth_NaturesCupboard2_Helper_Constants extends
Mage_Core_Helper_Abstract
{
	/**
	 * @var string  $_storeGroupName The name of the Store Group
	 */
	private static $_storeGroupName = 'Natures Cupboard';
	
	/**
	 * @var string $_storeViewCode The unique code for the Store View.
	 */
	private static $_storeViewCode = 'nc_default';
	
	/**
	 * Returns the Natures Cupboard Store View Code
	 */
	public function getNCStoreGroupName()
	{
		return self::$_storeGroupName;
	}
	
	/**
	 * Returns the Natures Cupboard Store View Code
	 */
	public function getNCStoreViewCode()
	{
		return self::$_storeViewCode;
	}
	

	
}