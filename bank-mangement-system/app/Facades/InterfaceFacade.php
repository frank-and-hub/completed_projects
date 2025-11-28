<?php 
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class InterfaceFacade extends Facade{
	protected static function getFacadeAccessor(){
		return 'cronefacade';
	}
}