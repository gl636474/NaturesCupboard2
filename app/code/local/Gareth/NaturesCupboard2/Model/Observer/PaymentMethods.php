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


class Gareth_NaturesCupboard2_Model_Observer_PaymentMethods extends Varien_Object
{
	/**
	 * Function called when the payment_method_is_active event is
	 * fired. Observer configured in config.xml.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function filterPaymentMethod($observer)
	{
		// Dont' filter if we are admin logged into back end
		/**
		 *  @var Mage_Admin_Model_Session $adminSession 
		 *  "admin/session" instance is instantiated in front end also
		 */
		$adminSession = Mage::getSingleton("admin/session");
		if ($adminSession->isLoggedIn() == false)
		{
			/** 
			 * @var Mage_Payment_Model_Method_Abstract $method 
			 * $method will be a concrete subclass
			 */
			$method = $observer->getEvent()->getMethodInstance();
			$methodCode = $method->getCode();
			/* @var Mage_Customer_Model_Session $customerSession */
			$customerSession = Mage::getSingleton("customer/session");
			$customerGroupId = (int)$customerSession->getCustomerGroupId();
			/* @var Gareth_NaturesCupboard2_Helper_Data $helper */
			$helper = Mage::helper('gareth_naturescupboard2/data');
			
			// First set the default. Then work through config to see if we have a
			// match for this method and customer group. NB Only set true if
			// originl value also true, hence AND-EQUALS logic
			$observer->getResult()->isAvailable &= $helper->getDefaultPaymentMethodAllowed();
			
			// Now search thru the methods/custgroups config table for an override
			$configTable = $helper->getPaymentMethodsConfigTable();
			foreach ($configTable as $methodConfig)
			{
				if ($methodConfig['method'] == $methodCode)
				{
					if (array_key_exists($customerGroupId, $methodConfig))
					{
						// NB AND-EQUALS so an existing false isn't overwritten
						$observer->getResult()->isAvailable &= $methodConfig[$customerGroupId];
						break;
					}
				}
			}
		}
	}
}