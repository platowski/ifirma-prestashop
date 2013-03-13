<?php
/**
	@author: Krystian Podemski, impSolutions.pl
	@release: 01.2013
	@version: 1.0
	@desc: Modul dla PrestaShop integrujacy ja z ifirma.pl, wersja dla PrestaShop 1.4++
**/
if (!defined('_PS_VERSION_'))
	exit;

class Ifirma extends Module
{

	public $order_instance;
	public $address_instance;
	public $customer_instance;

	public function __construct()
	{
		$this->name = 'ifirma';
		$this->tab = 'billing_invoicing';
		$this->version = '1.0';
		$this->author = 'impSolutions.pl & Power Media S.A.';
		$this->limited_countries = array('pl');

		parent::__construct();

		$this->displayName = 'Integracja z ifirma.pl';
		$this->description = 'Wystawiaj faktury w ifirma.pl';

		/** Backward compatibility */
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('adminOrder') OR !$this->installDB() OR !Configuration::updateValue('ifirma_hash',Tools::passwdGen(32)))
			return false;
		return true;
	}

	private function _postProcess()
	{
		if(Tools::isSubmit('submitIfirmaSettings'))
		{
			Configuration::updateValue('ifirma_api_vatowiec', Tools::getValue('ifirma_api_vatowiec'));
			Configuration::updateValue('ifirma_api_key_rachunek', Tools::getValue('ifirma_api_key_rachunek'));
			Configuration::updateValue('ifirma_api_key_faktura', Tools::getValue('ifirma_api_key_faktura'));
			Configuration::updateValue('ifirma_api_key_abonent', Tools::getValue('ifirma_api_key_abonent'));
			Configuration::updateValue('ifirma_api_login', Tools::getValue('ifirma_api_login'));
			$this->_html .= $this->displayConfirmation('Ustawienia zapisane');
		}
	}

	public function installDB()
	{
		$sql = 'CREATE TABLE IF NOT EXISTS `ifirma_invoice_map` (
				`map_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`document_type` VARCHAR(31) NULL,
				`cart_order_id` VARCHAR(31) NOT NULL,
				`invoice_number` VARCHAR(31) NOT NULL,
				`invoice_type` VARCHAR(31) NOT NULL,
				`correction_needed` INT(1) NOT NULL DEFAULT \'0\',
				`correction_done` INT(1) NOT NULL DEFAULT \'0\'
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
	
		return Db::getInstance()->execute($sql);
	}

	public function uninstallDB()
	{
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `ifirma_invoice_map`');
	}

	public function uninstall()
	{
		if (!parent::uninstall() 
			OR !$this->uninstallDB() 
			OR !Configuration::deleteByName('ifirma_api_vatowiec')
			OR !Configuration::deleteByName('ifirma_api_key_rachunek')
			OR !Configuration::deleteByName('ifirma_api_key_faktura')
			OR !Configuration::deleteByName('ifirma_api_key_abonent')
			OR !Configuration::deleteByName('ifirma_hash')
			OR !Configuration::deleteByName('ifirma_api_login'))
			return false;
		return true;
	} 

	public function getContent()
	{
		
		$this->_html = '';

		$this->_postProcess();
		$vat_checked = '';
		if(Tools::safeOutput(Tools::getValue('ifirma_api_vatowiec', Configuration::get('ifirma_api_vatowiec')))==true){
			$vat_checked = 'checked="checked"';
		}
		$this->_html .= '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" class="width4" style="margin: 0 auto;">
		<fieldset>
			<legend><img src="'._PS_ADMIN_IMG_.'prefs.gif" alt="Ustawienia" />Ustawienia</legend>
				
				<div style="clear: both; padding-top:15px;">
					<label class="conf_title" for="ifirma_api_vatowiec">Jestem płatnikiem Vat:</label>
					<div class="margin-form">	
						<input type="checkbox" name="ifirma_api_vatowiec" id="ifirma_api_vatowiec" '.$vat_checked.' />
						<p class="preference_description">Jestem płatnikiem Vat</p>	
					</div>
				</div><div style="clear: both; padding-top:15px;">
					<label class="conf_title" for="ifirma_api_key_rachunek">Klucz do API - rachunek:</label>
					<div class="margin-form">	
						<input type="text" name="ifirma_api_key_rachunek" id="ifirma_api_key_rachunek" value="'.Tools::safeOutput(Tools::getValue('ifirma_api_key_rachunek', Configuration::get('ifirma_api_key_rachunek'))).'" size="50" />
						<p class="preference_description">Klucz API rachunek</p>	
					</div>
				</div>
				<div style="clear: both; padding-top:15px;">
					<label class="conf_title" for="ifirma_api_key_faktura">Klucz do API - faktura:</label>
					<div class="margin-form">	
						<input type="text" name="ifirma_api_key_faktura" id="ifirma_api_key_faktura" value="'.Tools::safeOutput(Tools::getValue('ifirma_api_key_faktura', Configuration::get('ifirma_api_key_faktura'))).'" size="50" />
						<p class="preference_description">Klucz API faktura</p>	
					</div>
				</div>

				<div style="clear: both; padding-top:15px;">
					<label class="conf_title" for="ifirma_api_key_abonent">Klucz do API - abonent:</label>
					<div class="margin-form">
						<input type="text" name="ifirma_api_key_abonent" id="ifirma_api_key_abonent" value="'.Tools::safeOutput(Tools::getValue('ifirma_api_key_abonent', Configuration::get('ifirma_api_key_abonent'))).'" size="50" />
						<p class="preference_description">Klucz API abonent</p>	
					</div>
				</div>

				<div style="clear: both; padding-top:15px;">
					<label class="conf_title" for="ifirma_api_login">Login do API:</label>
					<div class="margin-form">
						<input type="text" name="ifirma_api_login" id="ifirma_api_login" value="'.Tools::safeOutput(Tools::getValue('ifirma_api_login', Configuration::get('ifirma_api_login'))).'" size="50" />
						<p class="preference_description">Twój login API</p>	
					</div>
				</div>

			<div class="clear center"><input class="button" style="margin-top: 10px" name="submitIfirmaSettings" id="submitIfirmaSettings" value="Zapisz" type="submit" /></div>
		</fieldset>
		</form><br/>';

		$this->_html .= '<fieldset class="width4" style="margin: 0 auto;">';
		$this->_html .= '<legend><img src="'._PS_ADMIN_IMG_.'comment.gif" alt="Informacje" />Informacje</legend>';
		$this->_html .= '<p><strong>W razie problemów: kontakt@impsolutions.pl, <a href="http://www.impsolutions.pl/?utm_source=ifirma&utm_medium=banner&utm_campaign=Ifirma.pl">www.impsolutions.pl</a></strong></p>';
		$this->_html .= '<p>Blog ifirma.pl - <a href="http://blog.ifirma.pl/">zobacz</a></p>';
		$this->_html .= '</fieldset>';
		return $this->_html;



	}

	public function hookAdminOrder()
	{
		if(Configuration::get('ifirma_api_login') == '' OR Configuration::get('ifirma_api_login') == '' OR Configuration::get('ifirma_api_login') == '') 
		{
			echo '<div class="warning">Ustaw dane API ifirma.pl by wystawiać faktury</div>';
			return;
		}

		include_once dirname(__FILE__).'/main/ifirma/ifirma_functions.php';
		$id = Tools::getValue('id_order');
		if($id > 0)
		{

			$this->smarty->assign('id_order',$id);
			$this->smarty->assign('hash',Configuration::get('ifirma_hash'));
			$this->smarty->assign('can_make_invoice',self::canMakeInvoice($id,'invoice'));
			$this->smarty->assign('is_vatowiec',Configuration::get('ifirma_api_vatowiec'));

			return $this->display(__FILE__,'order.tpl');
		}
			
	}

	public function prepareOrder($id_order)
	{
		$this->order_instance = new Order((int)$id_order);
		if(!Validate::isLoadedObject($this->order_instance)) die('Brak zamowienia o podanym ID');;

		$this->address_instance = new Address($this->order_instance->id_address_invoice);
		if(!Validate::isLoadedObject($this->address_instance)) die('Brak adresu o podanym ID');


		$this->customer_instance = new Customer($this->address_instance->id_customer);
		if(!Validate::isLoadedObject($this->customer_instance)) die('Brak klienta o podanym ID');

		$this->shipping = $this->order_instance->getShipping();
		

	}

	public function getPayType()
	{
		$pay_type = 'GTK';

		switch($this->order_instance->module)
		{
			case 'bankwire': 
			$pay_type = 'PRZ';
			break;

			case 'cashondelivery': 
			case 'cashondeliverywithfee':
			$pay_type = 'POB';
			break;

			case 'paypal': 
			$pay_type = 'PAL';
			break;

			case 'payu':
			case 'prestacafepayu':
			case 'platnoscipl': 
			case 'openpayu':
			$pay_type = 'ALG';
			break;

			case 'dotpay':
			case 'prestacafedotpay':
			$pay_type = 'DOT';
			break;

			case 'cheque':
			$pay_type = 'CZK';
			break;
		}

		return $pay_type;
	}

	public function isOrderWithoutShipping()
	{

		// echo '<pre>';
		// print_r($this->order_instance);
		// echo '</pre>';

		// die();

		$free_shipping = false;

		if (version_compare(_PS_VERSION_, '1.5', '<')) 
		{
			if($this->order_instance->total_shipping == 0 OR $this->order_instance->total_shipping == '') $free_shipping = true;
		}	
		else
		{
			if(sizeof($this->order_instance->getCartRules()))
			{
				foreach($this->order_instance->getCartRules() as $discount)
				{
					if($discount['free_shipping'] > 0) $free_shipping = true;
				}
			}
		}

		

		return $free_shipping;
	}

	public static function canMakeInvoice($id_order, $type)
	{
		$sql = "select * from ifirma_invoice_map where document_type = '".$type."' and cart_order_id = ".(int)$id_order;
		$invoices = DB::getInstance()->executeS($sql);

		if(sizeof($invoices)) return false;
		return true;
	}

	public static function makeInvoice($id_order, $invoice)
	{



	}
        
}
