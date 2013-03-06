<?php

/**
 * Description of FakturaWysylkowa
 *
 * @author platowski
 */
class FakturaWysylkowa extends BaseFlyweightBuilder{
	protected $fields = array('NumerKontaBankowego','DataOtrzymaniaZaplaty', 'MiejsceWystawienia','TerminPlatnosci','NazwaSeriiNumeracji',
		'NazwaSzablonu','PodpisOdbiorcy','PodpisWystawcy','Uwagi');
	public function __construct($zaplacono,$identyfikatorKontrahenta,$liczOd,$dataWystawienia, $formatDatySprzedazy,
		$rodzajPodpisuOdiorcy,$widocznyNumerGios, $numer,$dataSprzedazy, $kontrahent ){
		$this->details['Zaplacono'] = $zaplacono;
		$this->details['IdentyfikatorKontrahenta'] = $identyfikatorKontrahenta;
		$this->details['LiczOd'] = $liczOd;
		$this->details['DataWystawienia'] = $dataWystawienia;
		$this->details['FormatDatySprzedazy'] = $formatDatySprzedazy;
		$this->details['RodzajPodpisuOdbiorcy'] = $rodzajPodpisuOdiorcy;
		$this->details['WidocznyNumerGios'] = $widocznyNumerGios;
		$this->details['Numer'] = $numer;
		$this->details['DataSprzedazy'] = $dataSprzedazy;
		$this->details['Kontrahent'] = $kontrahent->getDetails();
		$this->details['Pozycje'] = array();
		$nip = $kontrahent->getNIP();
		if( !empty($nip) ){
			$this->details['NIPKontrahenta']=$kontrahent->getNIP();
		} 
		
	}
	public function dodajPozycjeFaktury($nowaPozycja){
		$this->details['Pozycje'][] = $nowaPozycja->getDetails();
	}
	public function getDataWystawienia(){
		return $this->details['DataWystawienia'];
	}
	public function getZaplacono(){
		return $this->details['Zaplacono'];
	}
	public function getPozycje(){
		return $this->details['Pozycje'];
	}
	public function setZaplacono($nowa_kwota){
		$this->details['Zaplacono'] = $nowa_kwota;
	}
	
}