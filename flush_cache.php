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

// specify the store view
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

try {
    $allTypes = Mage::app()->useCache();
    $allTypes = array_keys($allTypes);
    foreach($allTypes as $type) {
        Mage::app()->getCacheInstance()->cleanType($type);
        Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
        echo "{$type} cache cleared\n";
    }
    
    echo "Cleaning image cache\n";
    Mage::getModel('catalog/product_image')->clearCache();
    
    echo "Cleaning merged JS/CSS\n";
    Mage::getModel('core/design_package')->cleanMergedJsCss();
    Mage::dispatchEvent('clean_media_cache_after');
    
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
