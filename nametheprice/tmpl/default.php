<?php
/*------------------------------------------------------------------------
# default.php - template for the name the price (previously donation) input box
# ------------------------------------------------------------------------
# author    Reinhold Kainhofer, The Open Tools Association
# copyright Copyright (C) 2013 open-tools.net. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl.html GNU/GPL
# Websites: http://www.open-tools.net/
# Technical Support:  Forum - http://www.open-tools.net/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

	$class='vmcustom-nametheprice';
$field = $viewData[0];
if (!defined('VM_VERSION') or VM_VERSION < 3) { // VM2:
  $name = 'customPlugin['.$field->virtuemart_customfield_id.']['.$field->custom_element.'][customprice]';
} else {
  $name = 'customProductData['.$field->virtuemart_product_id.']['.$field->virtuemart_custom_id.']['.$field->virtuemart_customfield_id .'][customprice]';
}
?>

	<input class="<?php echo $class ?>" type="text" value="" size="6" name="<?php echo $name?>"><?php echo CurrencyDisplay::getInstance ()->getSymbol(); ?><br />
<?php
	// preventing 2 x load javascript
	static $namethepricejs;
	if ($namethepricejs) return true;
	$namethepricejs = true ;

	//javascript to update price
	$script = '
/* <![CDATA[ */
var namethepricefunc = function($) {
	jQuery(".vmcustom-nametheprice").keyup(function() {
			formProduct = $(this).parents("form.product");
			virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
		Virtuemart.setproducttype(formProduct,virtuemart_product_id);
		});
};
jQuery("body").on("updateVirtueMartProductDetail", namethepricefunc);
jQuery(document).ready(namethepricefunc);
/* ]]> */
	';
$document = JFactory::getDocument()->addScriptDeclaration($script);
	
