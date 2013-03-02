<?php
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 * A custom field plugin for downloadable files
 * @author Reinhold Kainhofer
 * @package VirtueMart
 * @subpackage vmcustom
 * @copyright Copyright (C) 2013 Reinhold Kainhofer - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://kainhofer.com
 */
if (!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');
// if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

if (JVM_VERSION === 2) {
    if (!defined('VMDONATION_PLUGINPATH')) define('VMDONATION_PLUGINPATH', JPATH_SITE.DS.'plugins'.DS.'vmcustom'.DS.'set_price');
    if (!defined('VMDONATION_PLUGINWEBROOT')) define('VMDONATION_PLUGINWEBROOT', 'plugins/vmcustom/set_price');
} else {
    if (!defined('VMDONATION_PLUGINPATH')) define('VMDONATION_PLUGINPATH', JPATH_SITE.DS.'plugins'.DS.'vmcustom');
    if (!defined('VMDONATION_PLUGINWEBROOT')) define('VMDONATION_PLUGINWEBROOT', 'plugins/vmcustom');
}

class plgVmCustomDonation extends vmCustomPlugin {

	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$varsToPush = array(
		);
		$this->setConfigParameterable('custom_params',$varsToPush);
	}


	/**
	 * @see Form displayed in the product edit page in the BE, configure the download file
	 * @author Reinhold Kainhofer
	 */
	function plgVmOnProductEdit($field, $product_id, &$row,&$retValue) {
		if ($field->custom_element != $this->_name) return '';
// 		$this->parseCustomParams($field);
// 		$html = '';
// 
// 		$retValue .= $html;
// 		$row++;
// 		return true ;
	}
	
	/**
	 * plgVmOnDisplayProductVariantFE ... Called for product variant custom fields to display on the product details page
	 */
	function plgVmOnDisplayProductVariantFE($field,&$row,&$group) {
		// default return if it's not this plugin
		if ($field->custom_element != $this->_name) return '';
		$this->getCustomParams($field);
		$group->display .= $this->renderByLayout('default',array($field,&$idx,&$group ) );
		return true;
	}

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCartModule()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCartModule( $product,$row,&$html) {
		return $this->plgVmOnViewCart($product,$row,$html);
	}

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCart()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCart($product,$row,&$html) {
		if (empty($product->productCustom->custom_element) or $product->productCustom->custom_element != $this->_name) return '';
		if (!$plgParam = $this->GetPluginInCart($product)) return '' ;

		foreach($plgParam as $k => $item){
			if(!empty($item['customprice']) && ($product->productCustom->virtuemart_customfield_id==$k)){
				$html .='<span>'.JText::_($product->productCustom->custom_title).' '.$item['customprice'].'</span>';
			}
		 }
		return true;
	}


	/**
	 * vendor order display BE
	 */
	function plgVmDisplayInOrderBE($item, $row, &$html) {
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		$this->plgVmOnViewCart($item,$row,$html); //same render as cart
	}

	/**
	 *
	 * shopper order display FE
	 */
	function plgVmDisplayInOrderFE($item, $row, &$html) {
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		$this->plgVmOnViewCart($item,$row,$html); //same render as cart
	}

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	public function plgVmOnStoreInstallPluginTable($psType, $name) {
		//Should the textinput use an own internal variable or store it in the params?
		//Here is no getVmPluginCreateTableSQL defined
// 		return $this->onStoreInstallPluginTable($psType);
	}


	function plgVmDeclarePluginParamsCustom($psType,$name,$id, &$data){
		return $this->declarePluginParams('custom', $name, $id, $data);
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

	public function plgVmCalculateCustomVariant($product, &$productCustomsPrice, $selected){
		if ($productCustomsPrice->custom_element !==$this->_name) return ;
		$customVariant = $this->getCustomVariant($product, $productCustomsPrice, $selected);
		if (!empty($customVariant['customprice'])) {
			$productCustomsPrice->custom_price = $customVariant['customprice'];
		} else {
			$productCustomsPrice->custom_price = 0.0;
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
	
    

// 	/**
// 	 * Create the database table for this plugin.
// 	 */
// 	public function getVmPluginCreateTableSQL() {
// 		return $this->createTableSQL('Downloads for Sale tracking');
// 	}
// 
// 	function getTableSQLFields() {
// 		$SQLfields = array(
// 		  'id' => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
// 		  'virtuemart_customfield_id' => 'int(11) UNSIGNED NOT NULL DEFAULT 0',
// 		  'virtuemart_order_item_id' => 'int(11) UNSIGNED NULL DEFAULT 0',
// 		  'downloaded' => 'int(11) UNSIGNED NOT NULL DEFAULT 0',
// 		);
// 		return $SQLfields;
// 	}

}

// No closing tag