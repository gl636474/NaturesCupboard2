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

/* @var $this Gareth_NaturesCupboard2_Model_Resource_Setup */
/* @var $installer Gareth_NaturesCupboard2_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

Mage::log('Running (SQL) upgrade-1.0.0-1.0.1 script', Zend_Log::NOTICE, 'gareth.log');

/**
 * Add a new column for the category ID, remove the old index which was keyed on
 * attribute_code and category_url_key. Create a new index on attribute_code and
 * category_id. The category_url_key column will be left as a sort of comment - 
 * unused programmatically.
 */

$mappingTableName = $installer->getTable('gareth_naturescupboard2/attribtocategorymapping');

$attrib_code_col_name = 'attribute_code';
$category_urlkey_col_name = 'category_url_key';
$category_id_col_name = 'category_id';

// add category_id column
$installer->getConnection()->addColumn($mappingTableName, $category_id_col_name, array(
		'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'unsigned' => true,
		'nullable' => false,
		'identity' => true,
		'default' => 0,
		'comment' => 'ID of the mapped category'
		));
Mage::log('Added '.$category_id_col_name.' column', Zend_Log::NOTICE, 'gareth.log');


// remove old index (drop category_url_key column)
$orig_index_fields = array($attrib_code_col_name, $category_urlkey_col_name);
$orig_index_name = $installer->getIdxName($mappingTableName, $orig_index_fields);
$installer->getConnection()->dropIndex($mappingTableName, $orig_index_name);
Mage::log('Removed index '.$orig_index_name, Zend_Log::NOTICE, 'gareth.log');

// add new index (add category_id column)
$new_index_fields = array($attrib_code_col_name, $category_urlkey_col_name);
$new_index_name = $installer->getIdxName($mappingTableName, $new_index_fields);
$installer->getConnection()->addIndex($mappingTableName, $new_index_name, $new_index_fields);
Mage::log('Added index '.$new_index_name, Zend_Log::NOTICE, 'gareth.log');

$installer->endSetup();
