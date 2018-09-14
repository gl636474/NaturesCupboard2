<?php

require_once 'app/Mage.php';

/**
 * Helper methods for install and upgrade scripts in
 * <code>data/naturescupboard2_setup<code> and 
 * <code>sql/naturescupboard2_setup<code>. The class running the sql/data setup
 * <code>$this</code> or <code>$installer</code> will be an instance of this
 * class, assuming the following in the <code>config.xml</code> file:
 * 
 * <pre>
 * <resources>
 *  <!-- ... -->
 *  <naturescupboard2_setup>
 *      <setup>
 *          <module>Gareth_NaturesCupboard2</module>
 *          <class>Gareth_NaturesCupboard2_Model_Resource_Mysql4_Setup</class>
 *      </setup>
 *      <!-- ... -->
 * </pre>
 */
class Gareth_NaturesCupboard2_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
	/**
	 * A regular expression for the name of the store to which to add
	 * categories, attributes, etc.. 
	 * @var string
	 */
	private static $_theStoreRegex = "/nature.?s.*cupboard/i";
	
	/**
	 * The group name under which our attributes are listed within the attribute
	 * sets (group name seen in admin pages only)
	 *
	 * @var string
	 */
	private static $_attributeSetGroupName = 'NC Product Info';
	
	/**
	 * Cached Mage_Catalog_Model_Resource_Eav_Mysql4_Setup instance.
	 */
	private static $_eavSetup = null;
	
	/**
	 * Values allowed for the 'type' parameter of addAttribute()
	 * 
	 * @var array
	 */
	const ALLOWED_ATTRIB_TYPES = array(
			'varchar', // for text and textarea
			'datetime', // for date and datetime
			'int', // for boolean and text (with validation)
			'text', // for text and textarea
			'decimal', // for weight, price and text (with validation)
			);
	
	/**
	 * Values allowed for the 'input' parameter of addAttribute()
	 *
	 * @var array
	 */
	const ALLOWED_ATTRIB_INPUTS = array(
			'text',
			'date',
			'datetime',
			'boolean', 
			'textarea',
			'price',
			'weight',
	);
	
	/**
	 * Values allowed for the 'frontend_class' parameter of addAttribute()
	 * 
	 * @see https://www.mihaimatei.com/add-custom-attribute-with-validation-setup-script
	 * @var array
	 */
	const ALLOWED_ATTRIB_CLASSES = array(
			'validate-digits', // integers
			'validate-number', // decimals
			'validate-not-negative-number', // positive decimals
			'validate-email',
			'validate-url',
			'validate-alpha',
			'validate-alphanum',
			'validate-no-html-tags',
			'validate-alphanum-with-spaces',
			'validate-date',
			'validate-percents',
	);
	
	
	/**
	 * Returns the Mage_Catalog_Model_Resource_Eav_Mysql4_Setup instance used to
	 * add attributes, attribute sets.
	 *
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
	 */
	private function _getEavSetup()
	{
		if (is_null(self::$_eavSetup))
		{
			/* @var self::$_eavSetup Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
			self::$_eavSetup = Mage::getResourceModel('catalog/eav_mysql4_setup', 'core_setup');
		}
		return self::$_eavSetup;
	}
	
	/**
	 * Returns a URL human readable friendly version of the given string.
	 * @param string $string string to URL-ise
	 * @return string a URL friendly version of $string
	 */
	private function create_slug($string)
	{
		$string = strtolower($string);
		$slug=preg_replace('/[^a-z0-9-]+/', '-', $string);
		return $slug;
	}
	
	/**
	 * Adds a new category or edits the existing category if a category with
	 * the same name exists in the store retrned by getStore(). A pre-extisting category
	 * will be moved under the new parent if necessary. Any new values will
	 * overwite existing values.
	 * 
	 * @param $name string The plaintext name of the category
	 * @param $urlKey string The URL
	 * @param $parent int|string|Mage_Catalog_Model_Category the new parent category or null for the current root category
	 * @param $description the text to show on the frontend category page.
	 * @param $properties array additional properties.
	 * @return Mage_Catalog_Model_Category the created or updated category
	 */
	public function addCategory($name, $url_key = null, $parent = null, $description = null, $properties = null)
	{
		/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		
		$defaultProperties = array(
				'is_active'=>true,
				'is_anchor'=>true,
				'display_mode'=>'PRODUCTS',
				'include_in_menu'=>true,
				'meta_title'=>$name.' Products',
				'meta_keywords'=>$name,
				'meta_description'=>$name,
				'custom_apply_to_products'=>null,
				'custom_design'=>null,
				'custom_design_from'=>null,
				'custom_design_to'=>null,
				'custom_layout_update'=>null,
				'custom_use_parent_settings'=>null,
				'default_sort_by'=>null,
				'description'=>$description,
				'image'=>null
		);
	
		if (is_array($properties))
		{
			$combinedProperties = $properties + $defaultProperties;
		}
		else
		{
			$combinedProperties = $defaultProperties;
		}
		
		if (is_null($url_key))
		{
			$url_key = $this->create_slug($name);
		}
		
		if (is_null($parent))
		{
			$rootCategoryId = $lookup->getStore(self::$_theStoreRegex)->getRootCategoryId();
			$parentCategory = Mage::getModel('catalog/category')->load($rootCategoryId);
		}
		else if (is_numeric($parent))
		{
			$parentCategory = Mage::getModel('catalog/category')->load($parent);
			if (is_null($parentCategory))
			{
				die("addCategory(): Cannot find parent category with ID: ".$parent);
			}
		}
		else if ($parent instanceof Mage_Catalog_Model_Category)
		{
			$parentCategory = $parent;
		}
		else
		{
			$parentCategory = $lookup->findCategory(self::$_theStoreRegex, $parent);
			if (is_null($parentCategory))
			{
				die("addCategory(): Cannot find parent category called: ".$parent);
			}
		}
		
		$category = $lookup->findCategory(self::$_theStoreRegex, $name);
		if (is_null($category))
		{
			$exists = false;
			$category = new Mage_Catalog_Model_Category();
			$category->setPath($parentCategory->getPath());
			$category->setParentId($parentCategory->getId());
		}
		else
		{
			// already exists - but do we need to move it?
			$exists = true;
			if ($category->getParentId() != $parentCategory->getId())
			{
				$category = $category->move($parentCategory->getId(), null);
			}
		}
		
		$category->setName($name);
		$category->setUrlKey($url_key);
		$category->setStoreId($lookup->getStore(self::$_theStoreRegex)->getId());
		
		foreach ($combinedProperties as $key=>$value)
		{
			$category->setData($key, $value);
		}
		
		$category->save();

		if (!$exists)
		{
			Mage::log('Added Category '.$name, Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			Mage::log('Edited Category '.$name, Zend_Log::NOTICE, 'gareth.log');
		}
		return $category;
	}
		
	/**
	 * Makes the named category the new root category for the store. The 
	 * category is created if it does not already exist.
	 *
	 * NOTE cannot delete a category if it is set as root category for any
	 * store - change store root category if no delete button 
	 *
	 * @param $name string The plaintext name of the category
	 * @return Mage_Catalog_Model_Category the created or updated category
	 */
	public function addRootCategory($name)
	{
		// Assume root category names are unique across all stores 
		/* @var $category Mage_Catalog_Model_Category */
		$categoriesCollection = Mage::getModel('catalog/category')->getCollection();
		$categoriesCollection->addAttributeToFilter('name', $name);
		if (count($categoriesCollection) > 0)
		{
			$category = $categoriesCollection->getFirstItem();
			Mage::log('Root category '.$name.' already exists', Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			$category = Mage::getModel('catalog/category');
			$category->setName($name);
			
			$url_key = $this->create_slug($name);
			$category->setUrlKey($url_key);
			$category->setStoreId(0); // bit of root category magic
			$category->setIsActive(true);
			$category->setDisplayMode('PRODUCTS');
			
			$parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
			$parentCategory = Mage::getModel('catalog/category')->load($parentId);
			$category->setPath($parentCategory->getPath());
			
			$category->save();
			Mage::log('Created new root category '.$name, Zend_Log::NOTICE, 'gareth.log');
		}		
		
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		/* @var Mage_Core_Model_Store $theStore */
		$theStore = $lookup->getStore(self::$_theStoreRegex);
		/* @var Mage_Core_Model_Store_Group $theStore */
		$theGroup = $theStore->getGroup();
		
		$currentRootId = $theGroup->getRootCategoryId();
		if ($currentRootId != $category->getId())
		{
			$theGroup->setRootCategoryId($category->getId());
			$theGroup->save();
			Mage::log('Set store root category to '.$name, Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			Mage::log('Category: '.$name.' already store root category', Zend_Log::NOTICE, 'gareth.log');
		}
		return $category;
	}
	
	/**
	 * Add attribute or updates existing attribute with the given name. Required
	 * keys in the properties array are:
	 *   * type - string SQL type in table
	 *   * input - string HTML field type in backend
	 *   * label - string name shown on frontend
	 *   * required - boolean
	 * 
	 * @see https://www.mihaimatei.com/add-custom-attribute-with-validation-setup-script
	 * @param $name string name of property
	 * @param $properties array required keys: type, input, label and required
	 * @param $admin boolean if true, the attribute will not be seen by the customer on the front end
	 * @return Mage_Eav_Model_Entity_Attribute the newly created/updated attribute
	 */
	public function addAttribute($name, $properties, $admin=false)
	{
		if (!array_key_exists('type', $properties)
				or !array_key_exists('input', $properties)
				or !array_key_exists('label', $properties)
				or !array_key_exists('required', $properties))
		{
			die('addAttribute: must have type,input,label,required keys in $properties array');
		}
		
		$backend_type = $properties['type'];
		if (!in_array($backend_type, self::ALLOWED_ATTRIB_TYPES, true))
		{
			die('addAttribute: bad $type: '.$backend_type);
		}
		
		$frontend_input = $properties['input'];
		if (!in_array($frontend_input, self::ALLOWED_ATTRIB_INPUTS, true))
		{
			die('addAttribute: bad $input: '.$frontend_input);
		}
		
		if (array_key_exists('frontend_class', $properties))
		{
			$frontend_class = $properties['frontend_class'];
			if (!in_array($frontend_class, self::ALLOWED_ATTRIB_CLASSES))
			{
				var_dump($properties);
				die('addAttribute: bad $frontend_class: '.$frontend_class);
			}
		}
		
		// add in some defaults
		$defaultProperties = array(
				'visible' => true,
				'user_defined' => true,
				'group' => '', // so does not get added to all sets
				'default' => null,
				'unique' => false,
				'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
				'searchable' => true,
				'visible_on_front' => true,
				'html_allowed_on_front' => false,
				'filterable_in_search' => true,
				'used_in_product_listing' => true,
				'used_for_sort_by' => true,
				'visible_in_advanced_search' => true,
				'position' => 0,
				
		);
		
		if (is_array($properties))
		{
			$combinedProperties = $properties + $defaultProperties;
		}
		else
		{
			$combinedProperties = $defaultProperties;
		}
		
		if ($admin)
		{
			$combinedProperties['visible_on_front'] = false;
			$combinedProperties['used_in_product_listing'] = false;
			$combinedProperties['filterable_in_search'] = false;
			$combinedProperties['used_for_sort_by'] = false;
			$combinedProperties['visible_in_advanced_search'] = false;
		}
		
		// We deal with PRODUCT attributes
		$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
		/* @var $eavSetup Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
		$eavSetup = $this->_getEavSetup();
		// addAttribute deals with adding an existing attribute
		$eavSetup->addAttribute($entityTypeId, $name, $combinedProperties);
		
		/* @var $model Mage_Eav_Model_Entity_Attribute */
		$model = Mage::getModel('eav/entity_attribute');
		$model->loadByCode($entityTypeId, $name);

		if ($admin)
		{
			Mage::log('Added/Edited Admin Attribute '.$name, Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			Mage::log('Added/Edited Attribute '.$name, Zend_Log::NOTICE, 'gareth.log');
		}
		return $model;
	}

	/**
	 * Add an admin only attribute or updates existing admin only attribute with
	 * the given name. An admin only attribute will not be seen by the 
	 * customer on the front end. Required keys in the properties array are:
	 *   * type - string SQL type in table
	 *   * input - string HTML field type in backend
	 *   * label - string name shown on frontend
	 *   * required - boolean
	 *   
	 * This function is just a wrapper for addAttribute setting $admin = true.
	 *
	 * @see https://www.mihaimatei.com/add-custom-attribute-with-validation-setup-script
	 * @param $name string name of property
	 * @param $properties array required keys: type, input, label and required
	 * @return Mage_Eav_Model_Entity_Attribute the newly created/updated attribute
	 */
	public function addAdminAttribute($name, $properties)
	{
		return $this->addAttribute($name, $properties, true);
	}
	
	/**
	 * Adds a new Attribute Set with the specified name. Groups and attributes
	 * are copied from the given parent set. If $name already exists then does
	 * nothing - i.e. does not copy a new attribue from parent to this set.
	 * 
	 * @param string $name Name of the new set to create
	 * @param int|string|Mage_Eav_Model_Entity_Attribute_Set $parentAttributeSet Name, ID or instance of the parent set.
	 * @return Mage_Eav_Model_Entity_Attribute_Set the newly added attribute set
	 */
	public function addAttributeSet($name, $parentAttributeSet = 'Default')
	{
		/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		
		if (! ($parentAttributeSet instanceof Mage_Eav_Model_Entity_Attribute_Set))
		{
			$parentAttributeSetName = $parentAttributeSet;
			$parentAttributeSet = $lookup->findAttributeSet($parentAttributeSet);
			if (is_null($parentAttributeSet))
			{
				die('Cannot find parent AttributeSet '.$parentAttributeSet);
			}
		}
		$parentAttributeSetId = $parentAttributeSet->getId();

		// We deal with PRODUCT attributes
		$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
		
		/* @var $model Mage_Eav_Model_Entity_Attribute_Set */
		$model = $lookup->findAttributeSet($name);
		if (is_null($model))
		{
			$exists =false;
			$model = Mage::getModel('eav/entity_attribute_set');
			$model->setAttributeSetName($name);
		}
		else
		{
			$exists = true;
		}
		$model->setEntityTypeId($entityTypeId);
		$model->validate();
		$model->save();
		$model->initFromSkeleton($parentAttributeSetId);
		$model->save();
		
		if (!$exists)
		{
			Mage::log('Added AttributeSet '.$name, Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			Mage::log('Edited AttributeSet '.$name, Zend_Log::NOTICE, 'gareth.log');
		}
		return $model;
	}
	
	/**
	 * Adds the given attribute to the given group in the given set. The group
	 * is created if it does not exist. If the attribute set has children, the
	 * attribute must be manually added to the children.
	 * 
	 * @param int|string|Mage_Eav_Model_Entity_Attribute $attribute attribute to add to $set
	 * @param int|string|Mage_Eav_Model_Entity_Attribute_Set $set set to which to add $attribute
	 * @param int|string $group ID or name of group within $set to add $atribute (defaults to $_attributeSetGroupName). Creates the group if it does not already exist. Does nothing if group already exists.
	 */
	public function addAttributeToSet($attribute, $set, $group = null)
	{
		// We deal with PRODUCT attributes
		$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
		
		/* @var $eavSetup Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
		$eavSetup = $this->_getEavSetup();
		
		if (is_null($group))
		{
			$group = self::$_attributeSetGroupName;
		}
		if ($attribute instanceof Mage_Eav_Model_Entity_Attribute)
		{
			$attribute = $attribute->getAttributeCode();
		}
		else
		{
			if ($eavSetup->getAttribute($entityTypeId, $attribute)==false)
			{
				die("Unknown attribute ".$attribute." in addAttributeToSet");
			}
		}
		if ($set instanceof Mage_Eav_Model_Entity_Attribute_Set)
		{
			$setName = $set->getAttributeSetName();
			$setId = $set->getId();
		}
		else
		{
			$setName = $eavSetup->getAttributeSet($entityTypeId, $set, 'attribute_set_name');
			$setId = $eavSetup->getAttributeSet($entityTypeId, $set, 'attribute_set_id');
			if ($setName==false or $setId==false)
			{
				die("Unknown attribute set ".$set." in addAttributeToSet");
			}
		}
		
		// addAttributeGroup() will update if group already exists
		$eavSetup->addAttributeGroup($entityTypeId, $setId, $group, 100);
		// group can be ID or name
		// set can be ID or name
		// attribute can be ID or code (internal name)
		// this function will create attribute and/or set if they don't exist! 
		$eavSetup->addAttributeToSet($entityTypeId, $setId, $group, $attribute);

		Mage::log('Added Attribute '.$attribute.' to set '.$setName, Zend_Log::NOTICE, 'gareth.log');
	}	
	
	/**
	 * Adds a rule to add any product to the specified category if the product
	 * has the specified attribuite set to true. Has no effect if the mapping
	 * already exists.
	 * 
	 * @param string $attribute the attribute or the code of the attribute to look for
	 * @param string $category the category or URL of the category to add products to
	 * @return the newly created mapping or the pre existing mapping.
	 */
	public function addAttributeToCategoryMapping($attribute, $category)
	{
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		
		// We deal with PRODUCT attributes
		$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
		
		/* @var $eavSetup Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
		$eavSetup = $this->_getEavSetup();
		
		if ($attribute instanceof Mage_Eav_Model_Entity_Attribute)
		{
			$attribute_code = $attribute->getAttributeCode();
		}
		elseif (is_string($attribute))
		{
			$attribute_code = $attribute;
			if ($eavSetup->getAttribute($entityTypeId, $attribute)==false)
			{
				die("Unknown attribute ".$attribute." in addAttributeToCategoryMapping");
			}
		}
		else
		{
			die("addAttributeToCategoryMapping(): Invalid attribute code: ".$attribute);
		}
		
		if ($category instanceof Mage_Catalog_Model_Category)
		{
			$category_url_key= $category->getUrlKey();
		}
		elseif (is_string($category))
		{
			if (is_null($lookup->findCategoryByUrlKey(self::$_theStoreRegex, $category)))
			{
				die("Unknown category ".$category." in addAttributeToCategoryMapping");
			}
			$category_url_key= $category;
		}
		else
		{
			die("addAttributeToCategoryMapping(): Invalid category: ".$category);
		}
		
		$attrib_category_mapping= $lookup->findAttributeToCategoryMapping($attribute_code, $category_url_key);
		if (is_null($attrib_category_mapping))
		{
			$attrib_category_mapping = new Gareth_NaturesCupboard2_Model_AttribToCategoryMapping();
			$attrib_category_mapping->setAttributeCode($attribute_code);
			$attrib_category_mapping->setCategoryUrlKey($category_url_key);
			$attrib_category_mapping->save();
			Mage::log('Added AttributeToCategoryMapping '.$attribute_code.' to '.$category_url_key, Zend_Log::NOTICE, 'gareth.log');
		}
		else 
		{
			Mage::log('AttributeToCategoryMapping ('.$attribute_code.' to '.$category_url_key.') already exists', Zend_Log::NOTICE, 'gareth.log');
		}
		return $attrib_category_mapping;
	}
	
	/**
	 * Removes an attribute to category mapping rule. Does nothig if no such
	 * rule exists.
	 *
	 * @see addAttributeToCategoryMapping($attribute_code, $category_url)
	 * @param string $attribute_code the code of the attribute to look for
	 * @param string $category_url the URL of the category to add products to
	 */
	public function removeAttributeToCategoryMapping($attribute_code, $category_url)
	{
		//TODO Implement removeAttributeToCategoryMapping
		die('removeAttributeToCategoryMapping not implemented');
	}
}
