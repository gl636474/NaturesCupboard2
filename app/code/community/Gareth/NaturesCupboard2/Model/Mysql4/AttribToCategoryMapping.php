<?php

class Gareth_NaturesCupboard2_Model_Mysql4_AttribToCategoryMapping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the id refers to the key field in your database table.
        $this->_init('attribtocategorymapping/attribtocategorymapping', 'id');
    }
}