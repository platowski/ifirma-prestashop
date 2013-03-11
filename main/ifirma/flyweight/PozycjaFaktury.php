<?php

/**
 * Description of PozycjaFaktury
 *
 * @author platowski
 */
class PozycjaFaktury extends BaseFlyweightBuilder{
	protected $fields = array('PKWiU');
	public function __construct($stawkaVAT,$ilosc,$cenaJednostkowa, $nazwaPelna,$jednostka,$typStawkiVat,$rabatx){
		$this->details['StawkaVat'] = $this->toUtf8($stawkaVAT);
		$this->details['Ilosc'] = $ilosc;
		$this->details['CenaJednostkowa'] = $cenaJednostkowa;
		$this->details['NazwaPelna'] = $this->toUtf8($nazwaPelna);
		$this->details['Jednostka'] = $this->toUtf8($jednostka);
		$this->details['TypStawkiVat'] = $typStawkiVat;
		$this->details['Rabat'] = $rabatx;
	}
	public function getCenaJednostkowa(){
		return $this->details['CenaJednostkowa'];
	}
	public function getStawkaVat(){
		return $this->details['StawkaVat'];
	}
}