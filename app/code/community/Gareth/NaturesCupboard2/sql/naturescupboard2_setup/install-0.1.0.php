<?php

/* @var $installer Gareth_NaturesCupboard2_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$mappingTableName = $installer->getTable('attribtocategorymapping/attribtocategorymapping');

/* getConnection returns Varien_Db_Adapter_Interface */
$success = $installer->getConnection()->dropTable($mappingTableName);

/* @var Varien_Db_Ddl_Table $mapping_table */
$mapping_table = $installer->getConnection()->newTable($mappingTableName);

$mapping_table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
		), 'DB ID - not used in any queries');

$attrib_code_col_name = 'attribute_code';
$mapping_table->addColumn($attrib_code_col_name, Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable' => false,
		), 'If a product has this attribute set to true then add it to the category ');

$category_urlkey_col_name = 'category_url_key';
$mapping_table->addColumn($category_urlkey_col_name, Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable' => false,
		), 'Category URL_KEY to which to add products with the specified attribute ');

$mapping_table_index_fields = array($attrib_code_col_name, $category_urlkey_col_name);
$mapping_table_index_name = $installer->getIdxName($mappingTableName, $mapping_table_index_fields);
$mapping_table->addIndex($mapping_table_index_name,
		$mapping_table_index_fields,
		array('type'=>Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE));

$installer->getConnection()->createTable($mapping_table);

$installer->endSetup(); 
