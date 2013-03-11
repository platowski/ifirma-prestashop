<?php
define('_PS_ADMIN_DIR_', getcwd());
define('PS_ADMIN_DIR', _PS_ADMIN_DIR_); // Retro-compatibility
include(_PS_ADMIN_DIR_.'/../../../config/config.inc.php');

require_once(dirname(__FILE__).'/ifirma/BuilderClasses.php');
require_once(dirname(__FILE__).'/ifirma/ifirma_functions.php');
require_once(dirname(__FILE__).'/../ifirma.php');
require(_PS_MODULE_DIR_.'ifirma/backward_compatibility/backward.php');

$hash = Tools::getValue('h');
if($hash != Configuration::get('ifirma_hash')) Tools::redirectAdmin('../../../');


$id_order = $_GET['cart_order_id'];

handle_invoice_download((int)$id_order);