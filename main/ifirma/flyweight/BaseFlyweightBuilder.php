<?php

/**
 * Description of BaseFlyweightBuilder
 *
 * @author platowski
 */
abstract class BaseFlyweightBuilder{
	protected $fields = array();
	protected $details=array();

	public function __call($method,$args){
		$this->verifyValidFunctionCall($method,$args);
		$this->details[$method] = $this->toUtf8($args[0]);
	}
	protected function verifyValidFunctionCall($method,$args){
		$this->verifyValidMethodCall($method);
		$this->verifyThatHasOnlyOneArgument($args);
	}
	protected function hasField($field){
		return in_array($field,$this->fields);
	}
	protected function verifyValidMethodCall($method){
		if(! $this->hasField($method) ){
			throw new Exception('unsupported field' + $method);
		}
	}
	protected function verifyThatHasOnlyOneArgument($args){
		if( count($args) != 1 ){
			throw new Exception('invalid number of parameters');
		}
	}
	public function __toString(){
		print_r($this->details);
	}
	public function toJson(){
		return json_encode($this->details);
	}
	public function getDetails(){
		return $this->details;
	}
	public function toUtf8($string){
		//return iconv('ISO-8859-2','UTF-8//TRANSLIT//IGNORE', $string);
		return $string;
	}
}