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
 * Maps a product attribute to a category. The intention is that if the
 * attribute value is true then the product should be added to the category.
 * 
 * Since v1.0.1, categories are now mapped by ID not their URL-key
 * 
 * @author gareth
 *
 * @method string getAttributeCode() get the code of the mapped attribute
 * @method void setAttributeCode(string) set the code of the mapped attribute
 * @method string getCategoryUrlKey() get the url-key of the mapped category (deprecated, ignored)
 * @method void setCategoryUrlKey(string) set the url-key of the mapped category (deprecated, ignored)
 * @method integer getCategoryId() get the ID of the mapped category
 * @method void setCategoryId(integer) set the ID of the mapped category
 */
class Gareth_NaturesCupboard2_Model_AttribToCategoryMapping extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('gareth_naturescupboard2/attribtocategorymapping');
    }
}