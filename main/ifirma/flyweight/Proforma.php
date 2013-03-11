<?php

/**
 * Description of Proforma
 *
 * @author platowski
 */
class Proforma extends BaseFlyweightBuilder{
	protected $fields = array('NumerKontaBankowego','MiejsceWystawienia','TerminPlatnosci',
		'NazwaSzablonu','PodpisOdbiorcy','PodpisWystawcy','Uwagi');
	
	public function __construct($identyfikatorKontrahenta,$liczOd,$typFakturyKrajowej,$dataWystawienia,$sposobZaplaty,
	$rodzajPodpisuOdiorcy,$widocznyNumerGios, $numer, $kontrahent ){
		$this->details['IdentyfikatorKontrahenta'] = $identyfikatorKontrahenta;
		$this->details['LiczOd'] = $liczOd;
		$this->details['TypFakturyKrajowej'] = $typFakturyKrajowej;
		$this->details['DataWystawienia'] = $dataWystawienia;
		$this->details['SposobZaplaty'] = $sposobZaplaty;
		$this->details['RodzajPodpisuOdbiorcy'] = $rodzajPodpisuOdiorcy;
		$this->details['WidocznyNumerGios'] = $widocznyNumerGios;
		$this->details['Numer'] = $numer;
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
}