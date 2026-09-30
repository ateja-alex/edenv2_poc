<?php

namespace App\Eden\Managements\Tests;

/**
 *
 * Ce fichier sert à signaler que Avoir_vente_test extends Document_test et non Element_test
 *
 **/
class Avoir_vente_test extends Document_test {

	/**
	 *
	 * On teste la suppression de l'élément
	 *
	 */
	protected function test_suppression($management) {
		
		return true;
	}
}