<?php

/**
 * 
 * array key first qui n'est disponible qu'à partir de php 7.3
 * 
 */

if(!function_exists('array_key_first')) {
	
	function array_key_first($array) {
		
		return array_keys($array)[0];
	}
}
