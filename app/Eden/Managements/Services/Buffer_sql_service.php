<?php

namespace App\Eden\Managements\Services;

class Buffer_sql_service {
	
	private $insert_par_table = array();
	private $active = false;
	
	/**
	 * 
	 * Active le buffer
	 * 
	 */
	public function active() {
		
		$this->active = true;
	}
	
	/**
	 * 
	 * Désactive le buffer
	 * 
	 */
	public function desactive() {
		
		$this->active = false;
	}
	
	/**
	 * 
	 * Retourne le statut actif / inactif du buffer
	 * 
	 */
	public function est_actif() {
		
		return $this->active;
	}

    /**
     *
     * Prépare un insert multiple
     *
     */
    public function insert($table, $informations) {

		if(!isset($this->insert_par_table[$table]))
			$this->insert_par_table[$table] = array();
		
		$this->insert_par_table[$table][] = $informations;
    }

    /**
     *
     * Execute les requêtes
     *
     */
    public function execute() {
		
		if(empty($this->insert_par_table))
			return;
		
		foreach($this->insert_par_table as $table => $lignes) {
			
			\DB::table($table)->insert($lignes);
		}
    }
}
