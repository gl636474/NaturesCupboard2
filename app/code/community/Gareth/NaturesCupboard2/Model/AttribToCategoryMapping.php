<?php

class Gareth_NaturesCupboard2_Model_AttribToCategoryMapping extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('attribtocategorymapping/attribtocategorymapping');
    }
}