<?php

namespace App\Models\Exceptions;

use \Exception;

class ValidationException extends Exception{

	private $error;
	public function __construct($error){
		parent::__construct('1');
		$this->error = $error;
	}

	public function getError(){
		return $this->error;
	}
}