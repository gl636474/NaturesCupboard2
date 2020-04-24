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

/* @var $this Gareth_NaturesCupboard2_Model_Resource_Setup */
/* @var $installer Gareth_NaturesCupboard2_Model_Resource_Setup */
$installer = $this;

/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
$constants = Mage::helper('gareth_naturescupboard2/constants');
/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
$lookup = Mage::helper('gareth_naturescupboard2/lookup');
/* @var Mage_Core_Model_Store $store */
$store = $lookup->getStore($constants->getNCStoreViewCode());


$config = array(
		'payment/banktransfer/active' => 1,
		'payment/banktransfer/title' => 'Bank Transfer',
		'payment/banktransfer/order_status' => 'pending',
		'payment/banktransfer/allowspecific' => 1,
		/* NB 'GB' is the code for the UK */ 
		'payment/banktransfer/specificcountry' => 'GB',
		'payment/banktransfer/min_order_total' => null,
		'payment/banktransfer/max_order_total' => null,
		'payment/banktransfer/sort_order' => 2,
		'payment/banktransfer/instructions' => 'We will contact you to send you our bank details via more secure means. If you have not supplied us with your phone number or email address, please use the contact web page to speak to Jen or the sales team.',
		/* relative to $MAGE_DIR/media/email/logo/ direcory */
		'design/email/logo' => 'naturescupboard/nc_logo_email.png',
		'design/email/logo_alt' => "Nature's Cupboard",
		'design/email/logo_width' => 450,
		'design/email/logo_height' => 100,
		'design/email/logo' => 'images/nc_logo.png',
		
		
		
);

$installer->setSystemConfigRaw($config, $store);



$installer->endSetup();
