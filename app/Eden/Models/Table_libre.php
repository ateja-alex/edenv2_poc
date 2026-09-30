<?php

namespace App\Eden\Models;

use Illuminate\Database\Eloquent\Model;

class Table_libre extends Model {

    protected $connection = "mysql";

    protected $table = 'eden_tableslibres';
	
    protected $primaryKey = 'id';
	
    public $timestamps = false;

    public $desactiver_gestion_traduction = false;
	
	/**
     * Récupère tous les champs libres pour une table libre
     */
    public function champs_libres() {
		
        return $this->hasMany('App\Eden\Models\Champ_libre', 'type_element', 'type_element');
    }

    

    /**
     * 
     * Utilisé pour récupérer element dans la bonne langue
     * 
     */
    public function getElementAttribute() {

        if($this->desactiver_gestion_traduction)
            return $this->attributes['element'];

        $nom_traduit = $this->attributes['element'];

        if(!empty($this->index_traduction))
            $nom_traduit = traduction($this->index_traduction.'.element');

        return $nom_traduit;
    }

    /**
     * 
     * Utilisé pour récupérer element en français
     * 
     */
    public function getElementFrAttribute() {
        
        return $this->attributes['element'];
    }

    /**
     * 
     * Utilisé pour récupérer element pluriel dans la bonne langue
     * 
     */
    public function getElementPlurielAttribute() {

        if($this->desactiver_gestion_traduction)
            return $this->attributes['element_pluriel'];

        $nom_traduit = $this->attributes['element_pluriel'];

        if(!empty($this->index_traduction))
            $nom_traduit = traduction($this->index_traduction.'.element_pluriel');

        return $nom_traduit;
    }

     /**
     * 
     * Utilisé pour récupérer element pluriel en français
     * 
     */
    public function getElementPlurielFrAttribute() { 

        return $this->attributes['element_pluriel'];
    }

    /**
     * 
     * Utilisé pour récupérer le nom de la table dans la bonne langue
     * 
     */
    public function getNomTableAttribute() {

        if($this->desactiver_gestion_traduction)
            return $this->attributes['nom_table'];

        $nom_traduit = $this->attributes['nom_table'];

        if(!empty($this->index_traduction))
            $nom_traduit = traduction($this->index_traduction.'.nom_table');

        return $nom_traduit;
    }

     /**
     * 
     * Utilisé pour récupérer le nom de la table en français
     * 
     */
    public function getNomTableFrAttribute() { 

        return $this->attributes['nom_table'];
    }
}
