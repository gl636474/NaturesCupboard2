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

class Gareth_NaturesCupboard2_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('naturescupboard2')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('naturescupboard2')->__('Disabled')
        );
    }
}