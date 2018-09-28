<?php

$magentoDir = '.';
$options = getopt("d:",array('mage-dir:'));
if (!empty($options['mage-dir']))
{
	$magentoDir = $options['mage-dir'];
}
if (!empty($options['d']))
{
	$magentoDir = $options['d'];
}

require_once $magentoDir.'/app/Mage.php';

// Any files created will have global read-write permissions
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
