<?php
/*------------------------------------------------------------------------
# default.php - template for the donation input box
# ------------------------------------------------------------------------
# author    Reinhold Kainhofer, The Open Tools Association
# copyright Copyright (C) 2013 open-tools.net. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl.html GNU/GPL
# Websites: http://www.open-tools.net/
# Technical Support:  Forum - http://www.open-tools.net/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

	$class='vmcustom-settext';

	<input class="<?php echo $class ?>" type="text" value="" size="4" name="customPlugin[<?php echo $viewData[0]->virtuemart_customfield_id ?>][<?php echo $this->_name?>][customprice]"><?php echo CurrencyDisplay::getInstance ()->getSymbol(); ?><br />
<?php
	// preventing 2 x load javascript
	static $textinputjs;
	if ($textinputjs) return true;
	$textinputjs = true ;
	//javascript to update price
	$document = JFactory::getDocument();
	$document->addScriptDeclaration('
/* <![CDATA[ */
jQuery(document).ready( function($) {
	jQuery(".vmcustom-textinput").keyup(function() {
			formProduct = $(this).parents("form.product");
			virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
		Virtuemart.setproducttype(formProduct,virtuemart_product_id);
		});

});
/* ]]> */
	');