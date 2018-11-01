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

/* @var $installer Gareth_NaturesCupboard2_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->enableLogging();

/**
 * Attributes
 **/


$attrib_organic = $installer->addAttribute('is_organic', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Organic',
		'required' => true,
		'position' => 0,
		'sort_order' => 0));

$attrib_glutenfree = $installer->addAttribute('is_gluten_free', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Gluten Free',
		'required' => true,
		'position' => 1,
		'sort_order' => 1));

$attrib_dairyfree = $installer->addAttribute('is_dairy_free', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Dairy Free',
		'required' => true,
		'position' => 2,
		'sort_order' => 2));

$attrib_ecofriendly = $installer->addAttribute('is_eco_friendly', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Eco-Friendly',
		'required' => true,
		'position' => 3,
		'sort_order' => 3));

$attrib_vegan = $installer->addAttribute('is_vegan', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Vegan',
		'required' => true,
		'position' => 4,
		'sort_order' => 4));

$attrib_noaddedsugar = $installer->addAttribute('is_no_added_sugar', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'No Added Sugar',
		'required' => true,
		'position' => 5,
		'sort_order' => 5));

$attrib_raw = $installer->addAttribute('is_raw', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Raw',
		'required' => true,
		'position' => 6,
		'sort_order' => 6));

$attrib_preservefree = $installer->addAttribute('is_preservative_free', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Preservative Free',
		'required' => true,
		'position' => 7,
		'sort_order' => 7));

$attrib_gmofree = $installer->addAttribute('is_gmo_free', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'GMO Free',
		'required' => true,
		'position' => 8,
		'sort_order' => 8));

$attrib_ingredients = $installer->addAttribute('ingredients', array(
		'type' => 'text',
		'input' => 'textarea',
		'label' => 'Ingredients',
		'required' => true,
		'position' => 9,
		'sort_order' => 9));

/**
 * Admin only attributes 
 */


$attrib_costprice = $installer->addAdminAttribute('cost_price', array(
		'type' => 'decimal',
		'input' => 'text',
		'frontend_class' => 'validate-not-negative-number',
		'label' => 'Cost Price',
		'default' => 0,
		'required' => true));

$attrib_packageheight = $installer->addAdminAttribute('package_height', array(
		'type' => 'int',
		'input' => 'text',
		'label' => 'Package Height',
		'frontend_class' => 'validate-digits',
		'default' => 0,
		'required' => true));

$attrib_packagewidth = $installer->addAdminAttribute('package_width', array(
		'type' => 'int',
		'input' => 'text',
		'label' => 'Package Width',
		'frontend_class' => 'validate-digits',
		'default' => 0,
		'required' => true));

$attrib_packagedepth = $installer->addAdminAttribute('package_depth', array(
		'type' => 'int',
		'input' => 'text',
		'label' => 'Package Depth',
		'frontend_class' => 'validate-digits',
		'default' => 0,
		'required' => true));

$attrib_food = $installer->addAdminAttribute('is_food', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Food Product',
		'required' => false,
		'default' => false));

$attrib_personal = $installer->addAdminAttribute('is_personal_care', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Personal Care Product',
		'required' => false,
		'default' => false));

$attrib_baby = $installer->addAdminAttribute('is_baby', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Baby Product',
		'required' => false,
		'default' => false));

$attrib_household = $installer->addAdminAttribute('is_household', array(
		'type' => 'int',
		'input' => 'boolean',
		'label' => 'Household Product',
		'required' => false,
		'default' => false));


		
		
/**
 * Attribute Sets
 **/


$attribset_product = $installer->addAttributeSet(
		'Natures Cupboard Product');

$installer->addAttributeToSet($attrib_organic, $attribset_product);
$installer->addAttributeToSet($attrib_ecofriendly, $attribset_product);
$installer->addAttributeToSet($attrib_vegan, $attribset_product);
$installer->addAttributeToSet($attrib_packageheight, $attribset_product);
$installer->addAttributeToSet($attrib_packagewidth, $attribset_product);
$installer->addAttributeToSet($attrib_packagedepth, $attribset_product);

$installer->addAttributeToSet($attrib_food, $attribset_product);
$installer->addAttributeToSet($attrib_personal, $attribset_product);
$installer->addAttributeToSet($attrib_baby, $attribset_product);
$installer->addAttributeToSet($attrib_household, $attribset_product);


$attribset_product_ingredients = $installer->addAttributeSet(
		'Natures Cupboard Product With Ingredients', 
		$attribset_product);

$installer->addAttributeToSet($attrib_ingredients, $attribset_product_ingredients);


$attribset_food_product = $installer->addAttributeSet(
		'Natures Cupboard Food Product',
		$attribset_product_ingredients);

$installer->addAttributeToSet($attrib_raw, $attribset_food_product);
$installer->addAttributeToSet($attrib_glutenfree, $attribset_food_product);
$installer->addAttributeToSet($attrib_dairyfree, $attribset_food_product);
$installer->addAttributeToSet($attrib_noaddedsugar, $attribset_food_product);
$installer->addAttributeToSet($attrib_preservefree, $attribset_food_product);
$installer->addAttributeToSet($attrib_gmofree, $attribset_food_product);


/**
 * Categories
 **/

$category_root = $installer->addRootCategory('Natures Cupboard Categories June 2016');
$store = $installer->createNaturesCupboardStore($category_root);
$installer->setStoreRootCategory($store, $category_root);

$category_food = $installer->addCategory('Food', $store, 'food', $category_root, "We endevour to bring you the healthiest food products - healthy for you, the environment and where appropriate the farmers/producers. Almost all of our range is organic - and where a product isn't there is a joly good reason. Most of our products are certified fairly traded or eco-friendly or both.");
$category_personal = $installer->addCategory('Personal Care', $store, 'personal-care', $category_root, "Hair and skin products that are as natural an organic as can be. Healthy for you, the environment and where appropriate the farmers/producers. Most of our products are certified fairly traded or eco-friendly or both.");
$category_baby = $installer->addCategory('Baby', $store, 'baby', $category_root, "Baby products that are as natural an organic as can be. We wouldn't want you to put any product on baby's skin that you wouldn't put in your mouth! Healthy for baby, the environment and where appropriate the farmers/producers. Most of our products are certified fairly traded or eco-friendly or both.");
$category_household = $installer->addCategory('Household', $store, 'household', $category_root, "Household cleaning products that are as natural an organic as can be. Healthier for you, the environment and where appropriate the farmers/producers. Most of our products are certified fairly traded or eco-friendly or both.", array('meta_title'=>'Household Items'));

$category_freefrom = $installer->addCategory('Free From', $store, 'free-from', $category_root, "Foods that are free from common allergens: gluten, dairy and sugar.", array('meta_title'=>'Free From Foods'));
$category_glutenfree = $installer->addCategory('Gluten Free', $store, 'gluten-free', $category_freefrom, "Foods that are certified to be free from gluten and therefore wheat-free too.", array('meta_title'=>'Gluten Free Foods'));
$category_dairyfree = $installer->addCategory('Dairy Free', $store, 'dairy-free', $category_freefrom, "Foods that are certified to be free from lactose and any dairy product.", array('meta_title'=>'Dairy Free Foods'));
$category_noaddedsugar = $installer->addCategory('No Added Sugar', $store, 'no-added-sugar', $category_freefrom, "Foods certified to have no added sugar. They may contain naturally occuring sugars contained within their ingredients. We have highlighted any cases where artificial sweeteners have been used.", array('meta_title'=>'Foods with No Added Sugar'));

$category_root_id = $category_root->getEntityId();
$all_products_layout_update = <<<EOT
<!-- This is a "dummy" category. When it's shown, we force its' category ID -->
<!-- to be the root category id and thus shows all products. -->
<reference name="product_list">
    <action method="setData">
        <key>category_id</key>
        <value>$category_root_id</value>
    </action>
</reference>
EOT;

$installer->addCategory('All', $store, 'all-products', $category_root, "All our products, from sandwich bags to baby shampoo and from cooking oil to gluten free snacks.",
		array(	'custom_layout_update'=>$all_products_layout_update));



/**
 * Attribute to Category Mappings - auto add a product to a specified category
 * if the product has a specified attribute set to true.
 **/

$installer->addAttributeToCategoryMapping($attrib_food, $category_food);
$installer->addAttributeToCategoryMapping($attrib_personal, $category_personal);
$installer->addAttributeToCategoryMapping($attrib_baby, $category_baby);
$installer->addAttributeToCategoryMapping($attrib_household, $category_household);

$installer->addAttributeToCategoryMapping($attrib_dairyfree, $category_dairyfree);
$installer->addAttributeToCategoryMapping($attrib_glutenfree, $category_glutenfree);
$installer->addAttributeToCategoryMapping($attrib_noaddedsugar, $category_noaddedsugar);


/**
 * Miscellaneous
 */
$installer->setStoreLogoPath("images/nc_logo.png", "images/nc_logo.png", "The Fairy Door Nature's Cupboard logo");
$installer->setPackageAndTheme('Gareth', 'NaturesCupboard2');
$installer->enableShippingMethod(null, false);
$installer->enableShippingMethod('gareth_royalmail');
$installer->endSetup(); 
