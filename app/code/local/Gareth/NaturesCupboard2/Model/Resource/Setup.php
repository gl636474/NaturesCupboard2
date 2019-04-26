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
	 * @var string  $_storeGroupName The name of the Store Group
	 */
	private static $_storeGroupName = 'Natures Cupboard';
	
	/**
	 * @var string $_storeViewCode The unique code for the Store View.
	 */
	private static $_storeViewCode = 'nc_default';
	
	/**
	 * @var string $_storeViewName The name of the Store View
	 */
	private static $_storeViewName = 'Default Store View';
	
	/**
	 * A regular expression for the name of the store to which to add
	 * categories, attributes, etc.. 
	 * @var string
	 * @deprecated
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
	 * Country codes that can be passed as country-type values to
	 * setSystemConfig(). For example:
	 * <pre>
	   $sys_config['general/country/default'] = 'AF';
	   $sys_config['general/country/allow'] = 'AF,US,GB';
	   setSystemConfig($sys_config);
	   </pre>
	 * @var array
	 */
	const ALLOWED_SYS_CONF_COUNTRIES = array(
			'AF','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU',
			'AT','AX','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BL',
			'BT','BO','BA','BW','BV','BR','IO','VG','BN','BG','BF','BI','KH',
			'CM','CA','CD','CV','KY','CF','TD','CL','CN','CX','CC','CO','KM',
			'CG','CK','CR','HR','CU','CY','CZ','DK','DJ','DM','DO','EC','EG',
			'SV','GQ','ER','EE','ET','FK','FO','FJ','FI','FR','GF','PF','TF',
			'GA','GM','GE','DE','GG','GH','GI','GR','GL','GD','GP','GU','GT',
			'GN','GW','GY','HT','HM','HN','HK','HU','IS','IM','IN','ID','IR',
			'IQ','IE','IL','IT','CI','JE','JM','JP','JO','KZ','KE','KI','KW',
			'KG','LA','LV','LB','LS','LR','LY','LI','LT','LU','ME','MF','MO',
			'MK','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU','YT','FX',
			'MX','FM','MD','MC','MN','MS','MA','MZ','MM','NA','NR','NP','NL',
			'AN','NC','NZ','NI','NE','NG','NU','NF','KP','MP','NO','OM','PK',
			'PW','PA','PG','PY','PE','PH','PN','PL','PS','PT','PR','QA','RE',
			'RO','RS','RU','RW','SH','KN','LC','PM','VC','WS','SM','ST','SA',
			'SN','SC','SL','SG','SK','SI','SB','SO','ZA','GS','KR','ES','LK',
			'SD','SR','SJ','SZ','SE','CH','SY','TL','TW','TJ','TZ','TH','TG',
			'TK','TO','TT','TN','TR','TM','TC','TV','VI','UG','UA','AE','GB',
			'US','UM','UY','UZ','VU','VA','VE','VN','WF','EH','YE','ZM','ZW'
	);
	
	/**
	 * Country codes that can be passed as country-type values to
	 * setSystemConfig(). For example:
	 * <pre>
	 $sys_config['general/country/default'] = 'AF';
	 $sys_config['general/country/allow'] = 'AF,US,GB';
	 setSystemConfig($sys_config);
	 </pre>
	 * @var array
	 */
	const ALLOWED_SYS_CONF_CURRENCIES = array(
			'AFN','ALL','DZD','AOA','ARS','AMD','AWG','AUD','AZN','AZM','BSD',
			'BHD','BDT','BBD','BYR','BZD','BMD','BTN','BOB','BAM','BWP','BRL',
			'GBP','BND','BGN','BUK','BIF','XPF','KHR','CAD','CVE','KYD','CLP',
			'CNY','COP','KMF','CDF','CRC','HRK','CUP','CZK','DKK','DJF','DOP',
			'XCD','EGP','GQE','ERN','EEK','ETB','EUR','FKP','FJD','GMD','GEK',
			'GEL','GHS','GIP','GTQ','GNF','GYD','HTG','HNL','HKD','HUF','ISK',
			'INR','IDR','IRR','IQD','ILS','JMD','JPY','JOD','KZT','KES','KWD',
			'KGS','LAK','LVL','LBP','LSL','LRD','LYD','LTL','MOP','MKD','MGA',
			'MWK','MYR','MVR','MRO','MUR','MXN','MDL','MNT','MAD','MZN','MMK',
			'NAD','NPR','ANG','TWD','NZD','NIC','NGN','KPW','NOK','OMR','PKR',
			'PAB','PGK','PYG','PEN','PHP','PLN','QAR','RHD','RON','ROL','RUB',
			'RWF','SHP','SVC','WST','SAR','RSD','SCR','SLL','SGD','SKK','SBD',
			'SOS','ZAR','KRW','LKR','SDG','SRD','SZL','SEK','CHF','SYP','STD',
			'TJS','TZS','THB','TOP','TTD','TND','TRY','TRL','TMM','USD','UGX',
			'UAH','AED','UYU','UZS','VUV','VEF','VEB','VND','CHE','CHW','XOF',
			'YER','ZMK','ZWD'
			
			);
	
	/**
	 * Codes that can be passed as email-identity-type values to
	 * setSystemConfig(). These appear in the admin
	 * pages as:
	 * <ul>
	 * <li>General (Owner)</li>
	 * <li>Sales Representative</li>
	 * <li>Customer Services</li>
	 * <li>Custom 1</li>
	 * <li>Custom 2</li>
	 * </ul>
	 * For example:
	 * <pre>
	 $sys_config['contacts/email/sender_email_identity'] = 'support';
	 setSystemConfig($sys_config);
	 </pre>
	 * @var array
	 */
	const ALLOWED_SYS_CONF_EMAIL_IDENTITIES = array(
			'general',
			'sales',
			'support',
			'custom1',
			'custom2',
			);
	
	/**
	 * String value-types set by setSystemConfig().
	 * @var array
	 */
	const DEFAULT_SYS_CONF_STRING_VALUES = array(
			'general/locale/timezone' => 'Europe/London',
			'general/locale/code' => 'en_GB',
			'general/locale/weekend' => '0,6',
			'general/store_information/name' => 'My Store',
			'general/store_information/phone' => null,
			'general/store_information/hours' => null,
			'general/store_information/merchant_vat_number' => null,
			'general/store_information/address' => null,
			'design/head/default_title' => 'My Store',
			'design/head/title_prefix' => null,
			'design/head/title_suffix' => null,
			'design/head/default_description' => null,
			'design/head/default_keywords' => null,
			'design/header/welcome' => 'Welcome, valued customer',
			'design/footer/copyright' => '&copy; 2018 Gareth Ladd. All Rights Reserved.',
			'design/footer/absolute_footer' => null,
			'trans_email/ident_general/email' => null,
			'trans_email/ident_general/name' => null,
			'trans_email/ident_sales/email' => null,
			'trans_email/ident_sales/name' => null,
			'trans_email/ident_support/email' => null,
			'trans_email/ident_support/name' => null,
			'trans_email/ident_custom1/email' => null,
			'trans_email/ident_custom1/name' => null,
			'trans_email/ident_custom2/email' => null,
			'trans_email/ident_custom2/name' => null,
			'contacts/email/recipient_email' => null,
			'catalog/productalert_cron/error_email' => null,
			'sitemap/generate/error_email' => null,
			'shipping/origin/region_id' => null,
			'shipping/origin/postcode' => null,
			'shipping/origin/city' => null,
			'shipping/origin/street_line1' => null,
			'shipping/origin/street_line2' => null,
	);
	
	// Define these three because we use them a lot!
	
	/** System config path for the base currency */
	const SYS_CONF_CURRENCY_BASE = 'currency/options/base';
	/** System config path for the default currency */
	const SYS_CONF_CURRENCY_DEFAULT = 'currency/options/default';
	/** System config path for the allowed currencies */
	const SYS_CONF_CURRENCY_ALLOW = 'currency/options/allow';
	
	/**
	 * Currency value-types set by setSystemConfig().
	 * @var array
	 */
	const DEFAULT_SYS_CONF_CURRENCY_VALUES = array(
			self::SYS_CONF_CURRENCY_BASE => 'GBP',
			self::SYS_CONF_CURRENCY_DEFAULT=> 'GBP',
			self::SYS_CONF_CURRENCY_ALLOW => 'GBP',
	);
	
	/**
	 * Number value-types set by setSystemConfig().
	 * @var array
	 */
	const DEFAULT_SYS_CONF_NUMERIC_VALUES = array(
			'general/locale/firstday' => 1,
	);
	
	/**
	 * Email value-types set by setSystemConfig(). See
	 * self::ALLOWED_SYS_CONF_EMAIL_IDENTITIES.
	 * 
	 * @see self::ALLOWED_SYS_CONF_EMAIL_IDENTITIES
	 * @var array
	 */
	const DEFAULT_SYS_CONF_EMAIL_IDENT_VALUES = array(
			'contacts/email/sender_email_identity' => 'support',
			'catalog/productalert/email_identity' => 'sales',
			'catalog/productalert_cron/error_email_identity' => 'support',
			'sitemap/generate/error_email_identity' => 'support'
	);
	
	/**
	 * Country value-types set by setSystemConfig().
	 * @var array
	 */
	const DEFAULT_SYS_CONF_COUNTRY_VALUES = array(
			'general/country/default' => 'GB',
			'general/country/allow' => 'GB',
			//'general/country/eu_countries' => 'GB', // use magento default - does not impact any other sys conf values
			'general/country/optional_zip_countries' => 'HK,IE,MO,PA', // Postal Code is Optional for these countries
			'general/store_information/merchant_country' => GB,
			'general/region/state_required' => 'AT,CA,EE,FI,FR,DE,LV,LT,RO,ES,CH,US',
			'tax/defaults/country' => 'GB',
			'shipping/origin/country_id' => 'GB',
	);
	
	/**
	 * Boolean value-types set by setSystemConfig().
	 * @var array
	 */
	const DEFAULT_SYS_CONF_BOOLEAN_VALUES = array(
			'general/region/display_all' => false,
			'web/seo/use_rewrites' => true,
			'design/head/demonotice' => false,
			'contacts/contacts/enabled' => true, // enable contact us form
			'catalog/frontend/list_allow_all' => true, // in show x per page
			'catalog/sitemap/tree_mode' => true, // hierarchical sietmap page
			'catalog/productalert/allow_price' => true,
			'catalog/productalert/allow_stock' => true,
			'cataloginventory/options/show_out_of_stock' => true,
			'cataloginventory/options/display_product_stock_status' => true, // Display products availability in stock in the frontend
			'sitemap/generate/enabled' => true,
			'sendfriend/email/enabled' => false,
			'newsletter/subscription/confirm' => true,
			'customer/create_account/generate_human_friendly_id' => true,
			'customer/startup/redirect_dashboard' => false,
			'customer/captcha/enable' => true,

			'tax/calculation/discount_tax' => true, // Magento advice for EU
					
			/* TODO figure out tax stuff
			'tax/calculation/price_includes_tax' => true,
			'tax/calculation/shipping_includes_tax' => true,
			'tax/calculation/apply_after_discount' => false, //false = apply tax before discount
			'tax/calculation/discount_tax' => true, // ignored if apply_after_discount is false
			'tax/display/type' => false, // false = display price includes tax
			'tax/display/shipping' => false, // false = display price includes tax
			'tax/cart_display/price' => false, // false = display price includes tax
			'tax/cart_display/subtotal' => false, // false = display price includes tax
			'tax/cart_display/shipping' => false, // false = display price includes tax
			'tax/sales_display/price' => false, // false = display price includes tax
			'tax/sales_display/subtotal' => false, // false = display price includes tax
			'tax/sales_display/shipping' => false, // false = display price includes tax
			*/

			'checkout/cart/redirect_to_cart' => false, // false = After Adding a Product do not Redirect to Shopping Cart
			
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
	 * @param integer|string|Mage_Core_Model_Store|Mage_Core_Model_Store_Group $store the store group to add the category to
	 * @param $urlKey string The URL
	 * @param $parent int|string|Mage_Catalog_Model_Category the new parent category or null for the current root category
	 * @param $description string the text to show on the frontend category page.
	 * @param $properties array additional properties.
	 * @return Mage_Catalog_Model_Category the created or updated category
	 */
	public function addCategory($name, $store, $url_key = null, $parent = null, $description = null, $properties = null)
	{
		/** @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		$storeGroup = $lookup->getStoreGroup($store);
		
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
			$rootCategoryId = $storeGroup->getRootCategoryId();
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
			$parentCategory = $lookup->findCategoryByName($storeGroup, $parent);
			if (is_null($parentCategory))
			{
				die("addCategory(): Cannot find parent category called: ".$parent);
			}
		}
		$storeId = $storeGroup->getId();
		/* @var Mage_Catalog_Model_Category $category */
		$category = $lookup->findCategoryByName($storeGroup, $name);
		if (is_null($category))
		{
			$exists = false;
			$category = new Mage_Catalog_Model_Category();
			$category->setName($name);
			$category->setPath($parentCategory->getPath());
			$category->setParentId($parentCategory->getId());
			$category->setStoreId($storeId);
		}
		else
		{
			// already exists - but do we need to move it?
			$exists = true;
			if ($category->getParentId() != $parentCategory->getId())
			{
				$category = $category->move($parentCategory->getId(), null);
			}
			
			// add store ID. Using setStore(id) will throw excpetion if
			// category is currently in a different store or is in 
			// multiple stores
			$storeIds = $category->getStoreIds();
			if (!array_key_exists($storeId, $storeIds))
			{
				$storeIds[$storeId] = $storeId;
			}
			$category->setStoreIds($storeIds);
		}

		// set non-identifying data
		$category->setUrlKey($url_key);
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
	 * @param string $name The plaintext name of the category
	 * @param integer|string|Mage_Core_Model_Store|Mage_Core_Model_Store_Group $store if not null, set the new root category as root category of this store group - group must exist
	 * @return Mage_Catalog_Model_Category the created or updated category
	 */
	public function addRootCategory($name, $store = null)
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
		
		if (!is_null($store))
		{
			// setRootCategory handles $store being id/name/object/etc			
			$this->setStoreRootCategory($store, $category);
		}
		
		return $category;
	}
	
	/**
	 * Sets the specified store to have the specified category as its root. Does
	 * nothing if the category is already the store's root category.
	 * 
	 * @param integer|string|Mage_Core_Model_Store|Mage_Core_Model_Store_Group $store the store group to set the root category of
	 * @param Mage_Catalog_Model_Category $rootCategory the new root category
	 * @return Mage_Catalog_Model_Category $rootCategory
	 */
	public function setStoreRootCategory($store, $rootCategory)
	{
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		/* @var Mage_Core_Model_Store_Group $theGroup */
		$theGroup = $lookup->getStoreGroup($store);
		
		$storeName = $theGroup->getName();
		$categoryName = $rootCategory->getName();
		
		$currentRootId = $theGroup->getRootCategoryId();
		if ($currentRootId != $rootCategory->getId())
		{
			$theGroup->setRootCategoryId($rootCategory->getId());
			$theGroup->save();
			Mage::log('Set store '.$storeName.' root category to '.$categoryName, Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			Mage::log('Category: '.$categoryName.' already store '.$storeName.' root category', Zend_Log::NOTICE, 'gareth.log');
		}
		return $rootCategory;
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
	 * @return string the newly created mapping or the pre existing mapping.
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
			if (is_null($lookup->findCategoryByUrlKey(self::$_storeGroupName, $category)))
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
	 * @param string $category_url_key the URL of the category to add products to
	 */
	public function removeAttributeToCategoryMapping($attribute_code, $category_url_key)
	{
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		
		/* @var Gareth_NaturesCupboard2_Model_AttribToCategoryMapping $attrib_category_mapping */
		$attrib_category_mapping = $lookup->findAttributeToCategoryMapping($attribute_code, $category_url_key);
		
		$attrib_category_mapping->delete();
		
		Mage::log('AttributeToCategoryMapping ('.$attribute_code.' to '.$category_url_key.') deleted', Zend_Log::NOTICE, 'gareth.log');
	}
	
	/**
	 * Returns the code of the specified store, returning null if null or
	 * invalid value is passed
	 * @param mixed $store the store to return the code of, can be null
	 * @return null|string the store code of $store
	 */
	protected function getStoreCode($store)
	{
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup = Mage::helper('gareth_naturescupboard2/lookup');
		
		$store = $lookup->getStore($store);
		if (!is_null($store))
		{
			$store_code = $store->getCode();
		}
		else
		{
			$store_code = null;
		}
		return $store_code;
	}
	
	/**
	 * Save a configuration change - as would be made via the 
	 * System->Configuration admin menu option.
	 * 
	 * The groups_value is an array of the form:
	 * <pre>
	 * $groups_value[$group]['fields'][$field]['value'] = $value;
	 * </pre>
	 * Where 'fields' and 'value' are preset/hardcoded. The $group refers to the
	 * collapsable parts on the right hand side of the admin system config page.
	 * The $field is the config parameter. The $section, $group and
	 * $field values can be got from the system_config.xml - just search for the
	 * label text as appears on the admin HTML page.
	 * 
	 * @param string $section the section as per the links on the left hand side of the admin System Config page
	 * @param array $groups_value a multidimantional array specifying group, field and value
	 */
	protected function saveConfig($section, $groups_values, $website_code = null, $store_code = null)
	{
		if (!empty($section) && !empty($groups_values))
		{
			// Set website and store to null to set the
			// "Default Config" (as per the "Current Configuration Scope"
			// select in to pleft of System Configuration pages).
			// Else website=base and store=default for the store view or
			// website=base and store=null for the website.
			/** @var Mage_Adminhtml_Model_Config_Data $config_data */
			$config_data = Mage::getModel('adminhtml/config_data');
			$config_data->setSection($section)
				->setWebsite($website_code)
				->setStore($store_code)
				->setGroups($groups_values)
				->save();
		}
	}
	
	/**
	 * Sets the frontend logo (top left) of a store to the specified path.
	 * 
	 * @param integer|string|Mage_Core_Model_Store $store the store to set the logo path of
	 * @param string $logo_path logo image file relative to skin/frontend/PACKAGE/MODULE
	 * @param string $small_logo_path small logo image file relative to skin/frontend/PACKAGE/MODULE
	 * @param string $alt_text the HTML ALT text for the above images
	 * 
	 * @see https://stackoverflow.com/questions/2474039/magento-update-store-logo-programmatically
	 */
	public function setStoreLogoPath($store, $logo_path = null, $small_logo_path = null, $alt_text = null)
	{		
		//create a groups array that has the value we want at the right location
		$groups_value = array();
		if (!empty($logo_path))
		{
			$groups_value['header']['fields']['logo_src']['value'] = $logo_path;
		}
		if (!empty($small_logo_path))
		{
			$groups_value['header']['fields']['logo_src_small']['value'] = $small_logo_path;
		}
		if (!empty($alt_text))
		{
			$groups_value['header']['fields']['logo_alt']['value'] = $alt_text;
		}
		
		$store_code = $this->getStoreCode($store);
		$this->saveConfig('design', $groups_value, null, $store_code);
		
		Mage::log('Logo set to: '.$logo_path, Zend_Log::NOTICE, 'gareth.log');
	}
	
	/**
	 * Enables logging to ./var/log.
	 * 
	 * @param string $system_log_file defsult log name (defaults to system.log)
	 * @param string $exceptions_log_file exceptions log name (defaults to exceptions.log)
	 */
	public function enableLogging($system_log_file = null, $exceptions_log_file = null)
	{
		//create a groups array that has the value we want at the right location
		$groups_value = array();
		$groups_value['log']['fields']['active']['value'] = 1;
		if (!empty($system_log_file))
		{
			$groups_value['log']['fields']['file']['value'] = $system_log_file;
		}
		if (!empty($exceptions_log_file))
		{
			$groups_value['log']['fields']['exception_file']['value'] = $exceptions_log_file;
		}
		
		$this->saveConfig('dev', $groups_value);
		
		Mage::log('Logging enabled.', Zend_Log::NOTICE, 'gareth.log');
	}
	
	/**
	 * Set the currently active package and theme.
	 * 
	 * @param integer|string|Mage_Core_Model_Store $store the store to set the package and theme of
	 * @param string $package Package name, mandatory
	 * @param string $theme Theme name, mandatory
	 */
	public function setPackageAndTheme($store, $package, $theme)
	{
		//create a groups array that has the value we want at the right location
		$groups_value = array();
		if (!empty($package))
		{
			$groups_value['package']['fields']['name']['value'] = $package;
		}
		if (!empty($theme))
		{
			$groups_value['theme']['fields']['templete']['value'] = $theme;
			$groups_value['theme']['fields']['skin']['value'] = $theme;
			$groups_value['theme']['fields']['layout']['value'] = $theme;
			$groups_value['theme']['fields']['default']['value'] = $theme;
		}
		
		$store_code = $this->getStoreCode($store);
		$this->saveConfig('design', $groups_value, null, $store_code);
		
		Mage::log('Package/Theme set to: '.$package.'/'.$theme, Zend_Log::NOTICE, 'gareth.log');
	}
	
	/**
	 * Creates the named store if it does not already exist in the specified
	 * website. If the store already exists, the root category will not be set
	 * by this function.
	 * 
	 * @see https://stackoverflow.com/questions/8309076/how-to-create-a-site-store-and-view-programatically-in-magento
	 * @param string $name the name of the store to create
	 * @param Mage_Catalog_Model_Category the root category for the above store
	 * @param string $websiteName optional name of website
	 * @return Mage_Core_Model_Store the pre-existing or newly created store
	 */
	public function createNaturesCupboardStore($rootCategory, $setAsDefaultStore = true, $websiteName = 'Main Website')
	{
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup = Mage::helper('gareth_naturescupboard2/lookup');
		
		$website = $lookup->getWebsite($websiteName);
		if (is_null($website))
		{
			die('Cannot find website '.$websiteName);
		}
		else
		{
			Mage::log('Found website '.$websiteName, Zend_Log::DEBUG, 'gareth.log');
		}
		
		$storeGroup = $lookup->getStoreGroup(self::$_storeGroupName);
		if (is_null($storeGroup))
		{
			// addStoreGroup
			Mage::log('Creating '.self::$_storeGroupName.' store group', Zend_Log::DEBUG, 'gareth.log');
			
			/** @var Mage_Core_Model_Store_Group $storeGroup */
			$storeGroup = Mage::getModel('core/store_group');
			$storeGroup->setWebsiteId($website->getId())
				->setName(self::$_storeGroupName)
				->setRootCategoryId($rootCategory->getId())
				->save();

			Mage::log('Created '.self::$_storeGroupName.' store group', Zend_Log::NOTICE, 'gareth.log');
		}
		
		$store = $lookup->getStore(self::$_storeViewCode);
		if (is_null($store))
		{
			// addStore
			Mage::log('Creating '.self::$_storeViewName.' store view', Zend_Log::DEBUG, 'gareth.log');
			
			/** @var Mage_Core_Model_Store $store */
			$store = Mage::getModel('core/store');
			$store->setCode(self::$_storeViewCode)
				->setWebsiteId($storeGroup->getWebsiteId())
				->setGroupId($storeGroup->getId())
				->setName(self::$_storeViewName)
				->setIsActive(1)
				->save();
			
			Mage::log('Created '.self::$_storeViewName.' store view', Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			Mage::log(self::$_storeGroupName.'/'.self::$_storeViewName.' store already exists', Zend_Log::NOTICE, 'gareth.log');
		}
		
		if ($setAsDefaultStore)
		{
			$website->setDefaultGroupId($storeGroup->getId())
				->save();
			$storeGroup->setDefaultStoreId($store->getId())
				->save();
			
			Mage::log('Set '.self::$_storeViewName.' as default store', Zend_Log::NOTICE, 'gareth.log');
		}
		
		return $store;
	}
	
	/**
	 * Returns an array of string which lists all carriers installed, both
	 * enabled and disabled ones.
	 * 
	 * @return array names of installed carriers.
	 */
	protected function getAllShippingMethods()
	{
		$carriersConfig = Mage::getStoreConfig('carriers');
		
		/* The getStoreConfig() function returns a nested array representing the
		 * XML but without the <sections> and <groups> tags. */
		$carriers = array_keys($carriersConfig);
		return $carriers;
	}
	
	/**
	 * Enables or disables one or more shipping methods, identified by code.
	 * These codes are defined in the carrier class and also under the <groups>
	 * tag in system.xml. If null is passed as the code then all installed
	 * carriers are enabled/disabled. Non-existent carriers are ignored (but a
     * log message is generated).
	 * 
	 * @param array|string $code the code(s) of the shipping method(s) to enable/disable or null which means all installed carriers.
	 * @param boolean $enable whether to enable (true) or disable (false)
	 */
	public function enableShippingMethod($code, $enable = true)
	{
		$allCarriers = $this->getAllShippingMethods();
		
		if (empty($code))
		{
			$carriers = $allCarriers;
		}
		elseif (is_string($code))
		{
			$carriers = array($code);
		}
		else
		{
			die('Unknown argument to enableShippingMethod: '.$code.'. Must be string or array of string.');
		}
		
		//create a groups array that has the value we want at the right location
		$groups_value = array();
		
		foreach ($carriers as $carrierCode)
		{
			if (in_array($carrierCode, $allCarriers))
			{
				$groups_value[$carrierCode]['fields']['active']['value'] = $enable;
			}
			else
			{
				Mage::log('Carrier '.$carrierCode.' ignored - does not exist.', Zend_Log::INFO, 'gareth.log');
			}
		}
		$this->saveConfig('carriers', $groups_value);
		
		$carrierNames = join(', ', $carriers);
		if ($enable)
		{
			Mage::log('Carrier(s) '.$carrierNames.' enabled.', Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			Mage::log('Carrier(s) '.$carrierNames.' disabled.', Zend_Log::NOTICE, 'gareth.log');
		}
	}
	
	/**
	 * Returns an array of string which lists all payment methods installed,
	 * both enabled and disabled ones.
	 *
	 * @return array names of installed payment methods.
	 */
	protected function getAllPaymentMethods()
	{
		$paymentConfig = Mage::getStoreConfig('payment');
		
		/* The getStoreConfig() function returns a nested array representing the
		 * XML but without the <sections> and <groups> tags. */
		$paymentMethods = array_keys($paymentConfig);
		return $paymentMethods;
	}
	
	/**
	 * Enables or disables one or more payment methods, identified by code.
	 * These codes are defined in the payemnt class and also under the <groups>
	 * tag in system.xml. If null is passed as the code then all installed
	 * methods are enabled/disabled. Non-existent methods are ignored (but a
	 * log message is generated).
	 * 
	 * Known methods:
	 * <ul>
	 * <li>ccsave</li>
	 * <li>checkmo</li>
	 * <li>free</li>
	 * <li>purchaseorder</li>
	 * <li>banktransfer</li>
	 * <li>cashondelivery</li>
	 * <li>authorizenet</li>
	 * <li>paypal_express</li>
	 * <li>paypal_express_bml</li>
	 * <li>paypal_direct</li>
	 * <li>paypal_standard</li>
	 * <li>paypaluk_express</li>
	 * <li>paypaluk_direct</li>
	 * <li>verisign</li>
	 * <li>paypal_billing_agreement</li>
	 * <li>payflow_link</li>
	 * <li>payflow_advanced</li>
	 * <li>hosted_pro</li>
	 * <li>googlecheckout</li>
	 * <li>paypaluk_express_bml</li>
	 * <li>authorizenet_directpost</li>
	 * <li>moneybookers_acc</li>
	 * <li>moneybookers_csi</li>
	 * <li>moneybookers_did</li>
	 * <li>moneybookers_dnk</li>
	 * <li>moneybookers_ebt</li>
	 * <li>moneybookers_ent</li>
	 * <li>moneybookers_gcb</li>
	 * <li>moneybookers_gir</li>
	 * <li>moneybookers_idl</li>
	 * <li>moneybookers_lsr</li>
	 * <li>moneybookers_mae</li>
	 * <li>moneybookers_npy</li>
	 * <li>moneybookers_pli</li>
	 * <li>moneybookers_psp</li>
	 * <li>moneybookers_pwy</li>
	 * <li>moneybookers_sft</li>
	 * <li>moneybookers_so2</li>
	 * <li>moneybookers_wlt</li>
	 * <li>moneybookers_obt</li>
	 * <li>paypal_wps_express</li>
	 * </ul>
	 *
	 * @param array|string $code the code(s) of the payment method(s) to enable/disable or null which means all installed methods.
	 * @param boolean $enable whether to enable (true) or disable (false)
	 */
	public function enablePaymentMethod($code, $enable = true)
	{
		$allMethods = $this->getAllPaymentMethods();
		
		if (empty($code))
		{
			$methods = $allMethods;
		}
		elseif (is_string($code))
		{
			$methods= array($code);
		}
		else
		{
			die('Unknown argument to enablePaymentMethod: '.$code.'. Must be string or array of string.');
		}
		
		//create a groups array that has the value we want at the right location
		$groups_value = array();
		
		foreach ($methods as $methodCode)
		{
			if (in_array($methodCode, $allMethods))
			{
				$groups_value[$methodCode]['fields']['active']['value'] = $enable;
			}
			else
			{
				Mage::log('Payment method '.$methodCode.' ignored - does not exist.', Zend_Log::INFO, 'gareth.log');
			}
		}
		$this->saveConfig('payment', $groups_value);
		
		$methodNames = join(', ', $methods);
		if ($enable)
		{
			Mage::log('Payment method(s) '.$methodNames.' enabled.', Zend_Log::NOTICE, 'gareth.log');
		}
		else
		{
			Mage::log('Payment method(s) '.$methodNames.' disabled.', Zend_Log::NOTICE, 'gareth.log');
		}
	}
	
	/**
	 * Returns all the allowable paths which can be supplied to 
	 * setSystemConfig() together with their default values.
	 * 
	 * @return array all settable syustem configuration paths and their default valules
	 */
	public function getSystemConfigDefaults()
	{
		return sort(array_merge(
				self::DEFAULT_SYS_CONF_BOOLEAN_VALUES,
				self::DEFAULT_SYS_CONF_COUNTRY_VALUES,
				self::DEFAULT_SYS_CONF_CURRENCY_VALUES,
				self::DEFAULT_SYS_CONF_EMAIL_IDENT_VALUES,
				self::DEFAULT_SYS_CONF_NUMERIC_VALUES,
				self::DEFAULT_SYS_CONF_STRING_VALUES));
	}

	/**
	 * Returns the "left join" of both arrays - returns all the key-value pairs
	 * of the defaults array and those values in the user supplied array where
	 * the key exists in the deaults array. Values from the user supplied array
	 * take precedence.
	 * 
	 * @param array $defaults the left hand array of the 'left join'
	 * @param array $user_values the right hand array of the 'left join'
	 * @param callable $converter a function taking one argument which will clean the values in the $user_values array.
	 * @return array $defaults but including any valid values in $user_values 
	 */
	protected function combineDefaultsAndUserValues(array $defaults, array $user_values, callable $converter, bool $useDefaults)
	{
		$joinedArrays = array();
		foreach ($defaults as $defaultKey=>$defaultValue)
		{
			if (array_key_exists($defaultKey, $user_values))
			{
				$userValue = $user_values[$defaultKey];
				$convertedValue = $converter($userValue);
				if (!is_null($convertedValue))
				{
					$joinedArrays[$defaultKey] = $convertedValue;
					Mage::log('SysConfig '.$defaultKey.' set to: '.$userValue, Zend_Log::NOTICE, 'gareth.log');
				}
				else
				{
					Mage::log('SysConfig '.$defaultKey.' not set because value invalid: '.$userValue, Zend_Log::NOTICE, 'gareth.log');
				}
			}
			else
			{
				if ($useDefaults && !is_null($defaultValue))
				{
					$joinedArrays[$defaultKey] = $defaultValue;
					Mage::log('SysConfig '.$defaultKey.' set to default value: '.$defaultValue, Zend_Log::NOTICE, 'gareth.log');
				}
			}
		}
		return $joinedArrays;
	}

	/**
	 * Converts 'system/config/path'=$value to and array suitable to pass to 
	 * saveConfig().
	 * 
	 * @param array $paths array of 'general/country/default' => 'GB' config paths and values
	 * @return array an array of the form $groups_values['general']['country']['fields']['default']['value'] = 'GB'
	 */
	protected function splitConfigPathsIntoArray(array $paths)
	{
		$groups_values = array();
		foreach ($paths as $path => $value)
		{
			list($section,$group,$field) = explode('/', $path);
			$groups_values[$section][$group]['fields'][$field]['value'] = $value;
		}
		return $groups_values;
	}
	
	/**
	 * Calls saveConfig() in the argument but ensures the various config values
	 * are consistent and set in the correct order so as not to cause a DB index
	 * error.
	 * 
	 * This method ensures:
	 * <ul>
	 * <li>when setting base or default currency, it is in the allowed list</li>
	 * <li>when setting allowed currencies, the base and default are allowed</li>
	 * <li>Config is set in such an order that default and base currencies are
	 * in the allowed list that is in the database.</li>
	 * </ul>
	 * 
	 * @param array $config and array of path to value. Permitted paths are: 'currency/options/base', 'currency/options/default' and 'currency/options/allow'. All others are ignored. This argument must not be null. E.g. array('currency/options/base'=>'GBP')
	 */
	protected function saveCurrencyConfig($config, $storeView = null)
	{
		// these values are the ones from the DB
		$currentBase = Mage::getStoreConfig('currency/options/base');
		$currentDefault = Mage::getStoreConfig('currency/options/default');
		$currentAllowed = explode(',', Mage::getStoreConfig('currency/options/allow'));
		
		// these values are those from the argument
		$newAllowed = null;
		$newBase = null;
		$newDefault = null;
		
		// flag to say whether currentAllowed has changed and we need to re-save
		$needResetCurrentAllowed = false;
		
		// Get any new allowed currencies
		if (!empty($config[self::SYS_CONF_CURRENCY_ALLOW]))
		{		
			$settingAllowed = true;
			$newAllowed = explode(',', strval($config[self::SYS_CONF_CURRENCY_ALLOW]));
		}
		else
		{
			// $newAllowed already null
			$settingAllowed = false;
		}
			
		// Get any new base currency 
		if (!empty($config[self::SYS_CONF_CURRENCY_BASE]))
		{
			$settingBase = true;
			$newBase = strval($config[self::SYS_CONF_CURRENCY_BASE]);
			if (!in_array($newBase, $currentAllowed))
			{
				$currentAllowed[] = $newBase;
				$needResetCurrentAllowed = true;
			}
		}
		else
		{
			// $newBase already null
			$settingBase = false;
			
			// if we are setting allowed but not setting base and the current
			// base is not in the new allow list then we need to change the
			// base currency. However we may need to first reset the current
			// allow list to include this new base currency to ensure DB
			// integrity at all times.
			if ($settingAllowed)
			{
				if (!in_array($currentBase, $newAllowed))
				{
					$newBase = $newAllowed[0];
					$settingBase = true;
					if (!in_array($newBase, $currentAllowed))
					{
						$currentAllowed[] = $newBase;
						$needResetCurrentAllowed = true;
					}
				}
			}
		}
		
		// Get any new default currency
		if (!empty($config[self::SYS_CONF_CURRENCY_DEFAULT]))
		{
			$settingDefault = true;
			$newDefault = strval($config[self::SYS_CONF_CURRENCY_DEFAULT]);
			if (!in_array($newDefault, $currentAllowed))
			{
				$currentAllowed[] = $newDefault;
				$needResetCurrentAllowed = true;
			}
		}
		else
		{
			// $newDefault already null
			$settingDefault = false;

			// if we are setting allowed but not setting default and the current
			// default is not in the new allow list then we need to change the
			// default currency. However we may need to first reset the current
			// allow list to include this new default currency to ensure DB
			// integrity at all times.
			if ($settingAllowed)
			{
				if (!in_array($currentDefault, $newAllowed))
				{
					$newDefault = $newAllowed[0];
					$settingDefault = true;
					if (!in_array($newDefault, $currentAllowed))
					{
						$currentAllowed[] = $newDefault;
						$needResetCurrentAllowed = true;
					}
				}
			}
		}
		
		if ($settingBase || $settingDefault)
		{
			// if we changing base/default we may need to update the current
			// allowed value to include the new base/default currency 
			if ($needResetCurrentAllowed)
			{
				$currentAllowedStr = join(',', $currentAllowed);
				$groups_values = array();
				$groups_values['options']['fields']['allow']['value']= $currentAllowedStr;
				$this->saveConfig('currency', $groups_values);
			}
			
			// now save base/default currency - we have already ensured that
			// the current allowed in the DB contains these new values 
			$groups_values = array();
			if ($settingBase)
			{
				$groups_values['options']['fields']['base']['value']= $newBase;
			}
			if ($settingDefault)
			{
				// TODO error here on setting default currency!?
				$groups_values['options']['fields']['default']['value']= $newDefault;
			}
			$this->saveConfig('currency', $groups_values);
		}
		
		// we have already ensured the base/default currency is in $newAllowed
		if ($settingAllowed)
		{
			$groups_values = array();
			$newAllowedStr = join(',', $newAllowed);
			$groups_values['options']['fields']['allow']['value']= $newAllowedStr;
			$this->saveConfig('currency', $groups_values);
		}
		
		Mage::log('Set System Config for section currency', Zend_Log::NOTICE, 'gareth.log');
	}
	
	/**
	 * Sets system configuration values. A number of configuration values will
	 * be set to sensible (but not Magento default) values. Any of these can be
	 * overridden in the argumnet.
	 * 
	 * @param array $path_values and array of path to value. E.g. array('general/country/default'=>'GB')
	 * @param $useDefaults string whether to set default values for configs not specified in $path_values
	 */
	public function setSystemConfig($path_values, $useDefaults)
	{
		/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
		$lookup= Mage::helper('gareth_naturescupboard2/lookup');
		
		// These converter function are declared as anonymous functions because
		// it is a bit dodgy to make a function pointer from an instance method
		// on a particular object - not sure how $this and $self would work
		
		/** Returns the string version of the argument */
		$stringConverter = function($arg) { return (string) $arg; };
		/** Returns the boolean version of the argument */
		$booleanConverter = function($arg) { return boolval($arg); };
		/** Returns the number version of the argument */
		$numericConverter = function($arg) {return intval($arg); };
		
		/**
		 * Returns the argument if each comma separated part of the argument
		 * string is in ALLOWED_SYS_CONF_COUNTRIES otherwise returns null. 
		 */
		$countryConverter = function($arg) {
			foreach (split(',',(string)$arg) as $arg_part) {
				if (!in_array($arg_part, $this::ALLOWED_SYS_CONF_COUNTRIES)) {
					return null;
				}
			}
			return $arg;
		};
		
		/**
		 * Returns the argument if each comma separated part of the argument
		 * string is in ALLOWED_SYS_CONF_EMAIL_IDENTITIES otherwise returns null.
		 */
		$emailConverter = function($arg) {
			foreach (split(',',(string)$arg) as $arg_part) {
				if (!in_array($arg_part, $this::ALLOWED_SYS_CONF_EMAIL_IDENTITIES)) {
					return null;
				}
			}
			return $arg;
		};
		
		/**
		 * Returns the argument if each comma separated part of the argument
		 * string is in ALLOWED_SYS_CONF_CURRENCIES otherwise returns null.
		 */
		$currencyConverter = function($arg) {
			foreach (split(',',(string)$arg) as $arg_part) {
				if (!in_array($arg_part, $this::ALLOWED_SYS_CONF_CURRENCIES)) {
					return null;
				}
			}
			return $arg;
		};

		// combine user set values and defaults and clean the values for each
		// type of value
		
		$stringConfigs = $this->combineDefaultsAndUserValues(
				self::DEFAULT_SYS_CONF_STRING_VALUES, $path_values,
				$stringConverter, $useDefaults);
		
		$booleanConfigs = $this->combineDefaultsAndUserValues(
				self::DEFAULT_SYS_CONF_BOOLEAN_VALUES, $path_values,
				$booleanConverter, $useDefaults);

		$numericConfigs = $this->combineDefaultsAndUserValues(
				self::DEFAULT_SYS_CONF_NUMERIC_VALUES, $path_values,
				$numericConverter, $useDefaults);
	
		$currencyConfigs = $this->combineDefaultsAndUserValues(
				self::DEFAULT_SYS_CONF_CURRENCY_VALUES, $path_values,
				$currencyConverter, $useDefaults);

		$emailConfigs = $this->combineDefaultsAndUserValues(
				self::DEFAULT_SYS_CONF_EMAIL_IDENT_VALUES, $path_values,
				$emailConverter, $useDefaults);

		$countryConfigs = $this->combineDefaultsAndUserValues(
				self::DEFAULT_SYS_CONF_COUNTRY_VALUES, $path_values,
				$countryConverter, $useDefaults);

		// merge all cleaned path=>value pairs into one array
		$config = array_merge($stringConfigs, $booleanConfigs, $numericConfigs,
				$currencyConfigs, $emailConfigs, $countryConfigs);
		
		// convert from path=>value to array format
		$sections_configs = $this->splitConfigPathsIntoArray($config);
		
		// and finally save
		$sectionCount = 1;
		foreach ($sections_configs as $section => $groups_values)
		{
			if ($section!='currency')
			{
				$this->saveConfig($section, $groups_values);
				Mage::log('Set System Config for section '.$section.' ('.$sectionCount++.' of '.count($sections_configs).')', Zend_Log::NOTICE, 'gareth.log');
			}
			else
			{
				// already done
				Mage::log('Skipped System Config for section currency ('.$sectionCount++.' of '.count($sections_configs).')', Zend_Log::NOTICE, 'gareth.log');
			}
		}
	}
}
