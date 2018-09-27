<?php

require_once './app/Mage.php';
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

try {
    $allTypes = Mage::app()->useCache();
    foreach($allTypes as $type => $value) {
        Mage::app()->getCacheInstance()->cleanType($type);
        Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
        echo "{$type} cache cleared\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
