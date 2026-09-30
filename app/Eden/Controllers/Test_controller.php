<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Champs_libres;

class Test_controller extends Controller {

	/**
	 *
	 * Execute une méthode test
	 *
	 */
    public function test($methode) {

		return $this->$methode();
    }

	public function test_element($type_element) {

		// Si on n'est pas sur la pré-prod, on ne lance pas le test
		if(env('APP_ENV') !== 'preprod')
			return response('Non testé', 401);

		// Sinon, on peut lancer le test
		$test = test($type_element)->teste();

		// Le test est un échec, on retourne une erreur 500
		if($test !== true)
			return json_encode(['success' => false, 'erreur' => $test]);

		return json_encode(['success' => $test]);
	}

	public function test_feature($feature) {
		$debug = debug_backtrace();

		\Storage::put('test'.rand().'.json', json_encode($debug));

		\Log::info('debut test feature');
		// \Log::info($debug);

		// Si on n'est pas sur la pré-prod, on ne lance pas le test
		if(env('APP_ENV') !== 'preprod')
			return response('Non testé', 401);

		// Sinon, on peut lancer le test
		$test = test($feature)->teste();
		\Log::info('resultat test');
		\Log::info($test);

		// Le test est un échec, on retourne une erreur 500
		if($test !== true)
			return json_encode(['success' => false, 'erreur' => $test]);

		return json_encode(['success' => $test]);
	}

}
