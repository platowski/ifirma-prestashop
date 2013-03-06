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


$id_order = Tools::getValue('cart_order_id');

$ifirma = new Ifirma();

if(Ifirma::canMakeInvoice($id_order, 'invoice'))
{

	$ifirma->prepareOrder($id_order);

	$name = !empty($ifirma->customer_instance->company) ? $ifirma->customer_instance->company : $ifirma->customer_instance->firstname.' '.$ifirma->customer_instance->lastname;

	$kontrahent = new KontrahentFlyweightBuilder($name, $ifirma->address_instance->postcode, $ifirma->address_instance->city);

	if(!empty($ifirma->address_instance->vat_number)) 
		$kontrahent->NIP($ifirma->address_instance->vat_number);

	$kontrahent->Ulica($ifirma->address_instance->address1.' '.$ifirma->address_instance->address2);

	if(!empty($ifirma->address_instance->phone) OR !empty($ifirma->address_instance->phone_mobile))
	{
		if(!empty($ifirma->address_instance->phone_mobile))
			$kontrahent->Telefon($ifirma->address_instance->phone_mobile);

		if(!empty($ifirma->address_instance->phone) && empty($ifirma->address_instance->phone_mobile))
			$kontrahent->Telefon($ifirma->address_instance->phone);
	}

	$kontrahent->Identyfikator('IFI'.$ifirma->customer_instance->id);

	$pay_type = $ifirma->getPayType();

	// Faktura
	$invoice = new Faktura(
		$ifirma->order_instance->total_paid_real,
		'IFI'.$ifirma->customer_instance->id,
		'BRT',
		date('Y-m-d'),
		'DZN',
		$pay_type,
		'BPO',
		false, 
		NULL,
		date('Y-m-d'),
		$kontrahent
		);

	// Pozycje faktury
	$products = $ifirma->order_instance->getProducts();

	foreach($products as $product)
	{

		if (version_compare(_PS_VERSION_, '1.5', '<')) 
		{
			$product_price = $product['product_price_wt'];
		}	
		else
		{
			$product_price = $product['unit_price_tax_incl'];
		}

		$unit = 'szt.';
		if(isset($product['unity']) && $product['unity'] != '') $unit = $product['unity'];

		//$rabat = ($product['reduction_percent'] > 0) ? $product['reduction_percent'] : NULL;

		// if($rabat == NULL && $product['reduction_amount'] > 0)
		// {
		// 	$old_price = $product['original_product_price'] + ($product['original_product_price'] * ($product['tax_rate'] / 100));
		// 	$rabat = 100 - ($product['unit_price_tax_incl'] * 100 / $old_price);
		// }

		$rabat = NULL;

		$invoice_position = new PozycjaFaktury($product['tax_rate'] / 100,$product['product_quantity'], $product_price, $product['product_name'], $unit, 'PRC', $rabat);
		$invoice->dodajPozycjeFaktury($invoice_position);
	}

	// Wysylka
	if($ifirma->isOrderWithoutShipping() == false)
	{

		foreach($ifirma->shipping as $s)
		{
			$tax = Tax::getCarrierTaxRate($s['id_carrier'], $ifirma->order_instance->id_address_delivery);

			$invoice_position = new PozycjaFaktury($tax / 100, 1, $s['shipping_cost_tax_incl'], 'Wysyłka - '.$s['state_name'], 'usł.', 'PRC', NULL);
			$invoice->dodajPozycjeFaktury($invoice_position);
		}
	}

	// Numer zamówienia w uwagach
	if (version_compare(_PS_VERSION_, '1.5', '<')) 
	{
		$invoice->Uwagi('Zamówienie #'.$ifirma->order_instance->id);
	}	
	else
	{
		$invoice->Uwagi('Zamówienie #'.$ifirma->order_instance->reference);
	}
	
	

	handle_invoice_generation((int)$id_order,$invoice);

}
else
{
	die('Blad wystawiania faktury, prawdopodobnie faktura istnieje dla tego zamowienia - w razie pytan skontaktuj sie z autorem modulu badz oblusga ifirma.pl');
}