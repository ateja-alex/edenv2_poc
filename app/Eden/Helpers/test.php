<?php

	/**
	 * 
	 * Test
	 * 
	 **/

	function test($type_element) {

		$classes = array(

			"\\App\\Managements\\Tests\\" . ucfirst($type_element) . "_test",
			"\\App\\Eden\\Managements\\Tests\\" . ucfirst($type_element) . "_test",
			"\\App\\Eden\\Managements\\Tests\\Element_test",
		);
	
		foreach($classes as $classe) {
			
			if(class_exists($classe)) {
				
				$test = new $classe();
				$test->type_element = $type_element;

				return $test;
			}
		}
	}