<?php

$instance_de_buffer_sql_service = false;

function buffer_sql() {
	
	global $instance_de_buffer_sql_service;
	
	if(empty($instance_de_buffer_sql_service))
		$instance_de_buffer_sql_service = service('buffer_sql');
	
	return $instance_de_buffer_sql_service;
}

