<?php
/**------------------------------------------------------------------------
# nametheprice.php - VirtueMart 2 plugin to let customers give the price they want to pay (e.g. for donations or vouchers)
# ------------------------------------------------------------------------
# @author    Reinhold Kainhofer, The Open Tools Association
# @copyright Copyright (C) 2013 Reinhold Kainhofer, open-tools.net. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl.html GNU/GPL
* @package VirtueMart
* @subpackage vmcustom
# Websites: http://www.open-tools.net/
# Technical Support:  Forum - http://www.open-tools.net/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

if (!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');
// if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

if (!defined('VMNAMETHEPRICE_PLUGINPATH'))
     define ('VMNAMETHEPRICE_PLUGINPATH', JPATH_SITE.DS.'plugins'.DS.'vmcustom'.DS.'nametheprice');
if (!defined('VMNAMETHEPRICE_PLUGINWEBROOT'))
     define ('VMNAMETHEPRICE_PLUGINWEBROOT', 'plugins/vmcustom/nametheprice');

class plgVmCustomNameThePrice extends vmCustomPlugin {

	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$varsToPush = array(
			'min_amount'=>array('0.0', 'char'),
			'max_amount'=>array('', 'char'),
		);
		if(!defined('VM_VERSION') or VM_VERSION < 3){
			$this->setConfigParameterable('custom_params', $varsToPush);
		} else {
			$this->setConfigParameterable('customfield_params', $varsToPush);
		}
	}

	/**
	 * @see Form displayed in the product edit page in the BE, configure the download file
	 * @author Reinhold Kainhofer
	 */
	function plgVmOnProductEdit($field, $product_id, &$row,&$retValue) {
		if ($field->custom_element != $this->_name) return '';
		
		if (!defined('VM_VERSION') or VM_VERSION < 3) {
			$this->parseCustomParams ($field); // Not needed in VM3!
			$paramName = 'custom_param';
		} else {
			$paramName = 'customfield_params';
		}
		$html ='
			<fieldset>
				<legend>'. JText::_('VMCUSTOM_NAMETHEPRICE') .'</legend>
				<table class="admintable">
					'.VmHTML::row('input','VMCUSTOM_NAMETHEPRICE_MIN', $paramName.'['.$row.'][min_amount]',$field->min_amount).
					VmHTML::row('input','VMCUSTOM_NAMETHEPRICE_MAX', $paramName.'['.$row.'][max_amount]',$field->max_amount).
					'<tr>
				</table>
			</fieldset>';
		$retValue .= $html;
		$row++;
		return true ;
	}
	
	// VM3
	function plgVmOnDisplayProductFEVM3(&$product, &$field) {

		if ($field->custom_element != $this->_name) return '';
		$field->display .= $this->renderByLayout('default',array($field) );

		return true;
	}
	
	// VM2 legacy
	function plgVmOnDisplayProductVariantFE($field,&$idx,&$group) {
		// default return if it's not this plugin
		if ($field->custom_element != $this->_name) return '';
		$this->getCustomParams($field);
		$group->display .= $this->renderByLayout('default',array($field ) );
		return true;
	}

	// VM2 legacy
	function plgVmOnViewCart($product,$row,&$html) {
		if (empty($product->productCustom->custom_element) or $product->productCustom->custom_element != $this->_name) return '';
		if (!$plgParam = $this->GetPluginInCart($product)) return '' ;

		foreach($plgParam as $k => $item){

			if(!empty($item['customprice']) && ($product->productCustom->virtuemart_customfield_id==$k)){
				$currency = CurrencyDisplay::getInstance ();
				$html .='<span>'.JText::_($product->productCustom->custom_title).' '.$currency->priceDisplay($item['customprice']).'</span>';
			}
		 }
		return true;
	}

	function plgVmOnViewCartVM3(&$product, &$productCustom, &$html) {
		if (empty($productCustom->custom_element) or $productCustom->custom_element != $this->_name) return false;

		if(empty($product->customProductData[$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id])) return false;
		$item = $product->customProductData[$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id];
		if(isset($item['customprice'])){
			$currency = CurrencyDisplay::getInstance ();
			$html .='<span>'.vmText::_($productCustom->custom_title).' '.$currency->priceDisplay($item['customprice']).'</span>';
		}
		return true;
	}

	function plgVmOnViewCartModuleVM3( &$product, &$productCustom, &$html) {
		return $this->plgVmOnViewCartVM3($product,$productCustom,$html);
	}

	function plgVmDisplayInOrderBEVM3( &$product, &$productCustom, &$html) {
		$this->plgVmOnViewCartVM3($product,$productCustom,$html);
	}

	function plgVmDisplayInOrderFEVM3( &$product, &$productCustom, &$html) {
		$this->plgVmOnViewCartVM3($product,$productCustom,$html);
	}

	function plgVmOnViewCartModule($product,$row,&$html) {
		return $this->plgVmOnViewCart($product,$row,$html);
	}

	function plgVmDisplayInOrderBE($item, $row, &$html) {
		return $this->plgVmOnViewCart($item,$row,$html); //same render as cart
	}
	function plgVmDisplayInOrderFE($item, $row, &$html) {
		return $this->plgVmOnViewCart($item,$row,$html); //same render as cart
	}

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	public function plgVmOnStoreInstallPluginTable($psType) {
		//Should the textinput use an own internal variable or store it in the params?
		//Here is no getVmPluginCreateTableSQL defined
// 		return $this->onStoreInstallPluginTable($psType);
	}


	// VM2 legacy
	function plgVmDeclarePluginParamsCustom($psType,$name,$id, &$data){
		return $this->declarePluginParams('custom', $name, $id, $data);
	}

	// VM3
	function plgVmDeclarePluginParamsCustomVM3 (&$data) {
		return $this->declarePluginParams ('custom', $data);
	}
	
	function plgVmGetTablePluginParams($psType, $name, $id, &$xParams, &$varsToPush){
		return $this->getTablePluginParams($psType, $name, $id, $xParams, $varsToPush);
	}
	function plgVmSetOnTablePluginParamsCustom($name, $id, &$table){
		return $this->setOnTablePluginParams($name, $id, $table);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmOnDisplayEdit($virtuemart_custom_id,&$customPlugin){
		return $this->onDisplayEditBECustom($virtuemart_custom_id,$customPlugin);
	}

	// VM2 legacy
	public function plgVmCalculateCustomVariant($product, &$productCustomsPrice, $selected){
		if ($productCustomsPrice->custom_element !==$this->_name) return ;
		$customVariant = $this->getCustomVariant($product, $productCustomsPrice, $selected);
		// bounds for "nametheprice": max/min value.
		if (!empty($customVariant['customprice'])) {
			// Error handling to display errors to the user, e.g. when the amount exceeds the min/max values
			// This does not yet work for the AJAX calls, because in VM2 the json can only contain the prices
			if (!empty($productCustomsPrice->min_amount) && ($customVariant['customprice']<$productCustomsPrice->min_amount)) {
				JFactory::getApplication()->enqueueMessage(JText::sprintf('VMCUSTOM_NAMETHEPRICE_ERROR_MIN_EXCEEDED', $selected['customprice'], $customfield->min_amount), 'warning');
				$customVariant['customprice'] = $productCustomsPrice->min_amount;
			}
			if (!empty($productCustomsPrice->max_amount) && ($customVariant['customprice']>$productCustomsPrice->max_amount)) {
				JFactory::getApplication()->enqueueMessage(JText::sprintf('VMCUSTOM_NAMETHEPRICE_ERROR_MAX_EXCEEDED', $selected['customprice'], $customfield->max_amount), 'warning');
				$customVariant['customprice'] = $productCustomsPrice->max_amount;
			}
			
			$productCustomsPrice->custom_price = $customVariant['customprice'];
		} else {
			$productCustomsPrice->custom_price = 0.0;
		}
		return true;
	}

	// VM3
	public function plgVmPrepareCartProduct(&$product, &$customfield, $selected, &$modificatorSum){
		if ($customfield->custom_element !==$this->_name) return ;

		if (!empty($selected['customprice'])) {
			// Error handling to display errors to the user, e.g. when the amount exceeds the min/max values
			if (!empty($customfield->min_amount) && ($selected['customprice']<$customfield->min_amount)) {
				JFactory::getApplication()->enqueueMessage(JText::sprintf('VMCUSTOM_NAMETHEPRICE_ERROR_MIN_EXCEEDED', $selected['customprice'], $customfield->min_amount), 'warning');
				$selected['customprice'] = $customfield->min_amount;
			}
			if (!empty($customfield->max_amount) && ($selected['customprice']>$customfield->max_amount)) {
				JFactory::getApplication()->enqueueMessage(JText::sprintf('VMCUSTOM_NAMETHEPRICE_ERROR_MAX_EXCEEDED', $selected['customprice'], $customfield->max_amount), 'warning');
				$selected['customprice'] = $customfield->max_amount;
			}
			
			$modificatorSum += $selected['customprice'];
		} else {
			$modificatorSum += 0.0;
		}

		return true;
	}


	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		$this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
// 		$this->createOrderLinesCustom($html,$item,$productCustom, $row );
	}
	function plgVmOnSelfCallFE($type,$name,&$render) {
		$render->html = '';
	}

}

// No closing tag