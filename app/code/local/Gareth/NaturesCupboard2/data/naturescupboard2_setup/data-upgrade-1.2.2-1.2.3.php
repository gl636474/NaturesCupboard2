<?php
/**
 * Natures Cupboard 2 Magento extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is copyright Gareth Ladd 2020. Not for public dissemination
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

/* @var Gareth_NaturesCupboard2_Helper_Constants $constants */
$constants = Mage::helper('gareth_naturescupboard2/constants');
/* @var Gareth_NaturesCupboard2_Helper_Lookup $lookup */
$lookup = Mage::helper('gareth_naturescupboard2/lookup');
/* @var Mage_Core_Model_Store $store */
$store = $lookup->getStore($constants->getNCStoreViewCode());

/**
 * Delete attributes: is_baby, is_household, is_food and is_personal_care and 
 * any associated values in catalog_product_entity_int_* tables (EAV tables)
 * and associated entries in naturescupboard2_attribtocategorymapping.
 */

const CODE_BABY = 'is_baby';
const CODE_HOUSEHOLD = 'is_household';
const CODE_FOOD = 'is_food';
const CODE_PERSONAL = 'is_personal_care';

$installer->removeAttributeToCategoryMapping(CODE_BABY, 'baby');
$installer->removeAttributeToCategoryMapping(CODE_HOUSEHOLD, 'household');
$installer->removeAttributeToCategoryMapping(CODE_FOOD, 'food');
$installer->removeAttributeToCategoryMapping(CODE_PERSONAL, 'personal-care');

$installer->removeAttribute(CODE_BABY);
$installer->removeAttribute(CODE_HOUSEHOLD);
$installer->removeAttribute(CODE_FOOD);
$installer->removeAttribute(CODE_PERSONAL);

/**
 * startSetup disables foreign key checks. So do the attrib removal stuff before
 * calling startSetup in order for cascade deletes to work.
 */
$installer->startSetup();

/**
 * Helper class to create categories, assign products and keep track of
 * successes and errors.
 * 
 * @var Gareth_NaturesCupboard2_Model_Resource_Setup $installer
 * @var Mage_Core_Model_Store store
 * 
 * @author gareth
 */
class Gareth_NaturesCupboard2_Setup123Helper
{
	/**
	 * Construct a new helper
	 * 
	 * @param Gareth_NaturesCupboard2_Model_Resource_Setup $installer setup class/installer to use to add categories, etc
	 * @param Mage_Core_Model_Store $store Store to which to add categories, etc
	 */
	function __construct($installer, $store)
	{
		$this->installer = $installer;
		$this->store = $store;
		
		// Record of all SKUs actually added to all categories 
		$this->skus_added = array();
		// Record of unknown SKUs
		$this->skus_skipped = array();
	}
	
	public function getSkusAdded()
	{
		return $this->skus_added;
	}
	
	public function getSkusSkipped()
	{
		return $this->skus_skipped;
	}
	
		/**
	 * Add a category
	 * 
	 * @param string $name name of category
	 * @param string $urlkey URL-Key of category
	 * @param Mage_Catalog_Model_Category $parent parent category
	 * @param string $blurb description
	 * @param string $meta_title title for search engines to display
	 * @param array $skus string array of product SKUs to add to category
	 * @return Mage_Catalog_Model_Category the newly created category
	 */
	public function addCategory($name, $urlkey, $parent, $blurb, $meta_title, $skus)
	{
		$additional_properties = array(
				'include_in_menu'=>false,
				'meta_title'=>$meta_title
		);
		$new_category = $this->installer->addCategory($name, $this->store, $urlkey, $parent, $blurb, $additional_properties);
		$new_skus_added = $this->installer->addSkusToCategory($new_category, $skus);
		$this->skus_added = array_unique(array_merge($this->skus_added, $new_skus_added));
		
		$new_skus_skipped = array_diff($skus, $new_skus_added);
		$this->skus_skipped = array_unique(array_merge($this->skus_skipped, $new_skus_skipped));
		return $new_category;
	}
}

$helper = new Gareth_NaturesCupboard2_Setup123Helper($installer, $store);

/* ********** Household ********** */

$category_household = $lookup->findCategoryByUrlKey($store, 'household');

$category_cleaning_blurb = "Household cleaning products that are as natural an organic as can be. Healthier for you and the environment.";
$helper->addCategory('Cleaning', 'cleaning', $category_household, $category_cleaning_blurb, 'Household Cleaning Items', array(
		'ECONL710',
		'ECZCAD22',
		'ECOFSS2',
		'GFOSN10',
		'DWSBL15',
		'ELDWT70',
		'ELRKT02',
		'SUMELWL1L',
		'ELTC7',
		
));

$category_kitchen_blurb = "Kitchen products that are as natural an as can be. Healthier for you and the environment.";
$helper->addCategory('Kitchen', 'kitchen', $category_household, $category_kitchen_blurb, 'Kitchen Items', array(
		'IYCAF',
		'IYCBP',
		'BWCBW50',
		'BWBWP',
		'IYCSB',
));

/* ********** Personal Care ********** */

$category_personal = $lookup->findCategoryByUrlKey($store, 'personal-care');
$usual_blurb = "as natural an as can be. Healthy for you, the environment and where appropriate the farmers/producers. Most of our products are certified fairly traded or eco-friendly or both";

$category_clothes_blurb = "Clothing that is $usual_blurb.";
$helper->addCategory('Clothes', 'clothing', $category_personal, $category_clothes_blurb, 'Clothing', array());

$category_soap_blurb = "Soaps that are $usual_blurb.";
$helper->addCategory('Soap', 'soap', $category_personal, $category_soap_blurb, 'Soaps', array(
		'ALTCMYYS95',
		'ALTLCS95',
		'ALTBGS95',
		'ALTTTES95',
		'ALTAVS95',
		'ALTLLS95',
		'ALTGM95',
		'GFOSN10',
		'EMSB',
));

$category_oils_blurb = "Essential oils that are $usual_blurb. Additionally, we try to bring you organic essential oils wherever possible.";
$helper->addCategory('Essential Oils', 'essential-oils', $category_personal, $category_oils_blurb, 'Essential Oils', array(
		'AOYYO',
		'AOLMO',
		'AOLVO',
		'AOSEO',
		'AOFEO',
		'AOTTO',
));

$category_bathroom_blurb = "Bathroom products that are $usual_blurb.";
$category_bathroom = $helper->addCategory('Bathroom', 'bathroom', $category_personal, $category_bathroom_blurb, 'Bathroom Products', array(
		'NCSP12',
		'NCPL22',
		'SGOCB',
		'OCP100',
		'OSGCB',
		'ELTT9',
));

$category_toothpaste_blurb = "Toothpastes that are $usual_blurb.";
$helper->addCategory('Toothpaste', 'toothpaste', $category_bathroom, $category_toothpaste_blurb, 'Toothpastes', array(
		'ALDENS100',
		'BIOMTP100',
		'JJSTP50',
		'KFTPMF100',
));

$category_toothbrush_blurb = "Toothbrushes that are $usual_blurb.";
$helper->addCategory('Toothbrushes', 'toothbrushes', $category_bathroom, $category_toothbrush_blurb, 'Toothbrushes', array(
		'HUMMBTB',
		'CHOETB',
		'JJTB',
));




$category_hair_blurb = "Hair and body products that are $usual_blurb.";
$category_hair = $helper->addCategory('Hair and Body', 'hair-and-body', $category_personal, $category_hair_blurb, 'Hair and Body Products', array());

$category_bodywash_blurb = "Body washing products that are $usual_blurb.";
$helper->addCategory('Bodywash', 'bodywash', $category_hair, $category_bodywash_blurb, 'Bodywash Products', array(
		'FINABW400',
		'FNBWHM',
));

$category_shampoo_blurb = "Shampoos that are $usual_blurb.";
$helper->addCategory('Shampoo', 'shampoo', $category_hair, $category_shampoo_blurb, 'Shampoos', array(
		'FINGFO400',
		'FINBLS400',
));

$category_conditioner_blurb = "Hair Conditioners that are $usual_blurb.";
$helper->addCategory('Conditioner', 'conditioner', $category_hair, $category_conditioner_blurb, 'Hair Conditioners', array(
		'FINDFC400',
));



/* ********** Food ********** */

$category_food = $lookup->findCategoryByUrlKey($store, 'food');

$usual_blurb = "- healthy for you, the environment and where appropriate the farmers/producers. Almost all of our range is organic - and where a product isn't there is a joly good reason. Most of our products are certified fairly traded or eco-friendly or both.";

$category_condiments_blurb = "We endevour to bring you the healthiest table condiments to flavour your prepared food to your personal taste $usual_blurb.";
$helper->addCategory('Condiments', 'condiments', $category_food, $category_condiments_blurb, 'Table Condiments', array(
		'MROACV500',
		'BITKP34',
		'PMHS30',
));

$category_baking_blurb = "We endevour to bring you the healthiest baking staples and sweeteners $usual_blurb.";
$helper->addCategory('Baking and Sweet Stuff', 'baking', $category_food, $category_baking_blurb, 'Baking and Sweeteners', array(
		'MWBSO284',
		'MOTAHI270',
		'MABSMO454',
		'HTHOMF370',
		'SLPOMS250',
		'SUCCS50',
		'SFMS25',
		'SUCN10',
		'CCCP18',
));


$category_snacks_blurb = "We endevour to bring you the healthiest snacks $usual_blurb.";
$helper->addCategory('Snacks', 'snacks', $category_food, $category_snacks_blurb, 'Healthy Snacks', array(
		'TWFOM',
));

$category_drinks_blurb = "We endevour to bring you the healthiest drinks $usual_blurb.";
$helper->addCategory('Drinks', 'drinks', $category_food, $category_drinks_blurb, 'Drinks', array(
		'JWPRU750',
		'BIOELD330',
));

$category_tea_blurb = "We endevour to bring you the healthiest teas $usual_blurb.";
$helper->addCategory('Tea', 'tea', $category_food, $category_tea_blurb, 'Teas', array(
		'CLOROO80',
		'HHOTRB20',
		'HAMRHT20',
		'YTBRV17',
		'HTOHT20',
));

$category_breakfast_blurb = "We endevour to bring you the healthiest breakfasts $usual_blurb.";
$helper->addCategory('Breakfast', 'breakfast', $category_food, $category_breakfast_blurb, 'Cereals and other Breakfast Foods', array(
		'NPOMR37',
		'POPO85',
));


$category_nutfruitseed_blurb = "We endevour to bring you the healthiest selection of nuts, dries fruits and seeds $usual_blurb.";
$category_nutfruitseed = $helper->addCategory('Nuts, Dried Fruits and Seeds', 'nuts-dried-fruits-seeds', $category_food, $category_nutfruitseed_blurb, 'Nuts, Dried Fruits and Seeds', array());

$category_nut_blurb = "We endevour to bring you the healthiest nuts $usual_blurb.";
$helper->addCategory('Nuts', 'nuts', $category_nutfruitseed, $category_nut_blurb, 'Nuts', array(
		'SUOWN37',
		'SUOAL50',
));

$category_fruit_blurb = "We endevour to bring you the healthiest dried fruits $usual_blurb.";
$helper->addCategory('Dried Fruit', 'dried-fruits', $category_nutfruitseed, $category_fruit_blurb, 'Dried Fruits', array(
		'SUMOR500',
		'SDGB100',
		'SUOSL50',
));

$category_seed_blurb = "We endevour to bring you the healthiest seeds $usual_blurb.";
$helper->addCategory('Seeds', 'seeds', $category_nutfruitseed, $category_seed_blurb, 'Seeds', array(
		'RAWOC450',
		'PSBSS33',
		'SUOSM25',
		'SUPS50',
));


$category_storecupboard_blurb = "We endevour to bring you the healthiest items to stock your storecupboard or pantry $usual_blurb.";
$category_storecupboard = $helper->addCategory('Store Cupboard', 'storecupboard', $category_food, $category_storecupboard_blurb, 'Storecupboard Items', array(
		'SUOWQU500',
		'SOBBR500',
		'NAAVO25',
		'SUBR75',
		'SCOEV6',
		'SCO320',
		'SCO65',
		'SPASS',
));

$category_herbs_blurb = "We endevour to bring you the healthiest herbs to stock your storecupboard or pantry $usual_blurb.";
$helper->addCategory('Herbs', 'herbs', $category_storecupboard, $category_herbs_blurb, 'Herbs', array(
		'OSBL10',
		'OSRO25',
		'OSTM25',
		'SOORG',
		'SOSAG',
		'SOBAS',
));

$category_spices_blurb = "We endevour to bring you the healthiest spices to stock your storecupboard or pantry $usual_blurb.";
$helper->addCategory('Spices', 'spices', $category_storecupboard, $category_spices_blurb, 'Spices', array(
		'OSPA25',
		'OSGC25',
		'OSGC40',
		'OSMS25',
		'OSFTT25',
		'SMOKP35',
		'SOWCL',
		'SOCOS',
		'SOGCM',
		'SOCUS',
		'SOCIN',
));

$category_pasta_blurb = "We endevour to bring you the healthiest pasta to stock your storecupboard or pantry $usual_blurb.";
$helper->addCategory('Pasta', 'pasta', $category_storecupboard, $category_pasta_blurb, 'Pasta', array(
		'DOVBRS500',
		'ECGLL25',
		'SUFRP50',
));


$category_tins_blurb = "We endevour to bring you the healthiest foods in cans $usual_blurb.";
$category_tins = $helper->addCategory('Tinned Foods', 'tins', $category_food, $category_tins_blurb, 'Tinned / Canned Foods', array(
		'SUOJKFT400',
));

$category_bakedbeans_blurb = "We endevour to bring you the healthiest baked beans in a can $usual_blurb.";
$helper->addCategory('Tinned Baked Beans', 'baked-beans', $category_tins, $category_bakedbeans_blurb, 'Tinned / Canned Baked Beans', array(
		'SUOBB400',
));

$category_tomatoes_blurb = "We endevour to bring you the healthiest tomatoes in a can $usual_blurb.";
$helper->addCategory('Tinned Tomatoes', 'tomatoes', $category_tins, $category_tomatoes_blurb, 'Tinned / Canned Tomatoes', array(
		'SUTPT40',
		'SUOCT400',
		'SUCT400',
));

$category_pulses_blurb = "We endevour to bring you the healthiest unprocessed beans, peas and lentils in cans $usual_blurb.";
$helper->addCategory('Tinned Pulses', 'pulses', $category_tins, $category_pulses_blurb, 'Tinned / Canned Pulses', array(
		'SUMOMB',
		'SUOHB400',
		'SUOCLB400',
		'SUOBUTB400',
		'SUOBOLB400',
		'SUOBCP400',
		'SUOBEB400',
		'SUOBLB400',
		'SUOKB400',
		'SUOADU400',
		'SUOGL400',
		'SUBBL400',
		'SUOCP400',
));


$num_products_added = count($helper->getSkusAdded());
Mage::log("data-upgrade-1.2.2-1.2.3: Total of $num_products_added products added to new categories", Zend_Log::NOTICE, 'gareth.log');

$added_skus = implode("\n", $helper->getSkusAdded());
Mage::log($added_skus, Zend_Log::DEBUG, 'gareth.log');

$num_products_skipped = count($helper->getSkusSkipped());
if ($num_products_skipped > 0)
{
	$skipped_skus = implode("\n", $helper->getSkusSkipped());
	Mage::log("data-upgrade-1.2.2-1.2.3: Skipped $num_products_skipped products:\n$skipped_skus", Zend_Log::INFO, 'gareth.log');
}