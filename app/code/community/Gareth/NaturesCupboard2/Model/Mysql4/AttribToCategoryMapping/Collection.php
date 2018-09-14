<?php

class Gareth_NaturesCupboard2_Model_Mysql4_AttribToCategoryMapping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('attribtocategorymapping/attribtocategorymapping');
    }
}