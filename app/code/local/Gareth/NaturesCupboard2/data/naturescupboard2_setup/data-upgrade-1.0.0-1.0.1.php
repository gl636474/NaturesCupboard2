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

/* @var $this Gareth_NaturesCupboard2_Model_Resource_Setup */
/* @var $installer Gareth_NaturesCupboard2_Model_Resource_Setup */
$installer = $this;

// Some constants
$group_name_prices = 'Prices';
$attribset_name_product = 'Natures Cupboard Product';
$attribset_name_product_ingredients = 'Natures Cupboard Product With Ingredients';
$attribset_name_food_product = 'Natures Cupboard Food Product';

// Start the upgrade
$installer->startSetup();

$installer->editAttribute('cost', 'is_required', true);
$installer->editAttribute('cost', 'apply_to', null);

// Move cost from General group into Prices group, with price and margins
$installer->addAttributeToSet('cost', $attribset_name_product, $group_name_prices);
$installer->addAttributeToSet('cost', $attribset_name_product_ingredients, $group_name_prices);
$installer->addAttributeToSet('cost', $attribset_name_food_product, $group_name_prices);

// New attribute: Margin (£)
$attrib_margin_pounds = $installer->addAdminAttribute('margin_pounds', array(
		'type' => 'decimal',
		'input' => 'price',
		'label' => 'Margin (£)',
		'required' => false,
		'default' => 0));

$installer->addAttributeToSet($attrib_margin_pounds, $attribset_name_product, $group_name_prices);
$installer->addAttributeToSet($attrib_margin_pounds, $attribset_name_product_ingredients, $group_name_prices);
$installer->addAttributeToSet($attrib_margin_pounds, $attribset_name_food_product, $group_name_prices);

// New attribute: Margin (%)
$attrib_margin_percent = $installer->addAdminAttribute('margin_percent', array(
		'type' => 'decimal',
		'input' => 'text',
		'label' => 'Margin (%)',
		'required' => false,
		'default' => 0));

$installer->addAttributeToSet($attrib_margin_percent, $attribset_name_product, $group_name_prices);
$installer->addAttributeToSet($attrib_margin_percent, $attribset_name_product_ingredients, $group_name_prices);
$installer->addAttributeToSet($attrib_margin_percent, $attribset_name_food_product, $group_name_prices);




$installer->endSetup();
