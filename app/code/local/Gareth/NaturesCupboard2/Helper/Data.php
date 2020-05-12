<?php

class Gareth_NaturesCupboard2_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_PAYMENTS_CONFIG_TABLE = 'payment/allow/methods_config_table';
	
	/**
	 * Returns un-serialized data of the custom config field one
	 *
	 * @return array
	 */
	public function getPaymentMethodsConfigTable()
	{
		$config = Mage::getStoreConfig(self::XML_PATH_PAYMENTS_CONFIG_TABLE);
		
		if (!$config)
		{
			return array();
		}
		
		try 
		{
			$config = Mage::helper('core/unserializeArray')->unserialize($config);
		} 
		catch (Exception $exception)
		{
			Mage::logException($exception);
			Mage::Log('Exception retrieving '.XML_PATH_PAYMENTS_CONFIG_TABLE.' config: '.$exception, Zend_Log::NOTICE, 'gareth.log');
			$config = array(); // Return empty array if failed to un-serialize data
		}
		
		return $config;
	}
}