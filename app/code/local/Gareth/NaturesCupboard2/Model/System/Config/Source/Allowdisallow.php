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
 * Used in creating options for Allow|Disallow config value selection
 *
 */
class Gareth_NaturesCupboard2_Model_System_Config_Source_Allowdisallow
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('gareth_naturescupboard2/data')->__('Allow')),
            array('value' => 0, 'label'=>Mage::helper('gareth_naturescupboard2/data')->__('Disallow')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('gareth_naturescupboard2/data')->__('Disallow'),
            1 => Mage::helper('gareth_naturescupboard2/data')->__('Allow'),
        );
    }

}
