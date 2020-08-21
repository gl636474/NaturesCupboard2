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

/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
$lookup = Mage::helper('gareth_naturescupboard2/lookup');

/* @var Mage_Eav_Model_Entity_Attribute $attrib_package_height */
$attrib_package_height = $lookup->findAttribute('package_height');
$attrib_package_height->setDefaultValue(null);
$attrib_package_height->save();

/* @var Mage_Eav_Model_Entity_Attribute $attrib_package_width */
$attrib_package_width = $lookup->findAttribute('package_width');
$attrib_package_width->setDefaultValue(null);
$attrib_package_width->save();

/* @var Mage_Eav_Model_Entity_Attribute $attrib_package_depth */
$attrib_package_depth = $lookup->findAttribute('package_depth');
$attrib_package_depth->setDefaultValue(null);
$attrib_package_depth->save();

$installer->endSetup();
