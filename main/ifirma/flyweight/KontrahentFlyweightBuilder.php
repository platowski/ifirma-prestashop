<?php

/**
 * Description of KontrahentFlyweightBuilder
 *
 * @author platowski
 */
class KontrahentFlyweightBuilder extends BaseFlyweightBuilder{
	protected $fields = array('Identyfikator','PrefiksUE','NIP','Ulica','Kraj','Email','Telefon');
	public function __construct($nazwa,$kod,$miejscowosc){
		$this->details['Nazwa'] = $this->toUtf8($nazwa);
		$this->details['KodPocztowy'] = $kod;
		$this->details['Miejscowosc'] = $this->toUtf8($miejscowosc);
	}
	public function getNIP(){
		return $this->details['NIP'];
	}
	public function getIdentyfikator(){
		return $this->details['Identyfikator'];
	}
}