<?php

namespace App\Eden\Managements\Tests;

use Illuminate\Support\Arr;

class Element_test {

	public $type_element = false;
	public $management = false;

	/*
	 *
	 * On va réaliser les différents tests
	 *
	 */
	public function teste() {

		$this->management = management($this->type_element);

		$retour = $this->test_creation();

		if($retour !== true)
			return $retour;

		// On supprime l'élément créé
		$retour = $this->test_suppression($this->management);
		
		if($retour !== true)
			return $retour;

		return $retour;
	}


	/**
	 *
	 * On teste la création de l'élément
	 *
	 **/
	protected function test_creation() {
		
		$donnees = $this->donnees_test_enregistrement($this->type_element);
		
		return $this->management->enregistre($donnees);
	}

	/**
	 *
	 * On teste la suppression de l'élément
	 *
	 **/
	protected function test_suppression($management) {
		
		$retour = $management->supprime();
		
		if($retour !== true) {
			
			exception("Erreur dans la suppression d'un élément (".$management->_type_element."), erreur retournée : ".$retour);
		}
		
		return true;
	}

	/**
	 * 
	 * Génère des données de test
	 * 
	 **/
	protected function donnees_test_enregistrement($type_element) {
		
		$table_libre = table_libre($type_element);
		$champs_libres = $table_libre->champs_libres()->where('obligatoire', 1)->get()->pluck('nom_sql');

		$donnees = [];

		foreach ($champs_libres as $nom_sql) {

			$champ = management($type_element)->champ($nom_sql);
			
			if($champ->modele->type == 0 || $champ->modele->type == 6)
				$donnees[$nom_sql] = 'test '.date('Y-m-d H:i:s');
			
			elseif($champ->modele->type == 1 || $champ->modele->type == 10 || $champ->modele->type == 11 || $champ->modele->type == 12)
				$donnees[$nom_sql] = Arr::first($champ->liste_valeurs());
			
			elseif($champ->modele->type == 2 || $champ->modele->type == 3)
				$donnees[$nom_sql] = 42;
			
			elseif($champ->modele->type == 4)
				$donnees[$nom_sql] = date('Y-m-d');
			
			elseif($champ->modele->type == 5)
				$donnees[$nom_sql] = date('Y-m-d H:i:s');
			
			elseif($champ->modele->type == 9)
				$donnees[$nom_sql] = '#ff2020';
			
			elseif($champ->modele->type == 20)
				$donnees[$nom_sql] = Arr::first($champ->valeurs_initiales);

			elseif($champ->modele->type == 42) {
				
				$premiere_donnee = modele($champ->modele->type_element_ajax)->first();
				
				if(!empty($premiere_donnee))
					$donnees[$nom_sql] = $premiere_donnee->id;
			}
			

			/*
				7 => import de fichier (varchar)  (input type file)
			*/
		}
		
		return $donnees;
	}
	
	/**
	 *
	 * On teste la création de l'élément
	 *
	 **/
	protected function creation_element($type_element, $donnees_forcees = array()) {

        $management = management($type_element);
		$donnees = $this->donnees_test_enregistrement($type_element);
		
		foreach($donnees_forcees as $nom_sql => $valeur) {
			
			$donnees[$nom_sql] = $valeur;
		}
		
        $retour = $management->enregistre($donnees);

        if($retour !== true) {
            exception("Erreur dans la création d'un élément ($type_element), erreur retournée : ".$retour);
        }

        return $management;
	}
	
	/**
	 * 
	 * On valide un document de gestion commerciale
	 * 
	 */
	protected function valide_document($document) {
		
		$retour = $document->valide();
		
		if($retour !== true)
			exception("Erreur dans la validation d'un document ($type_element), erreur retournée : ".$retour);
		
		return $document;
	}
	
	/**
	 * 
	 * On transforme un document de gestion commerciale
	 * 
	 */
	protected function transforme_document($document, $type_element_destination) {
		
		list($retour, $nouveau_document_management) = $document->transformer_document($type_element_destination);
		
		if($retour !== true)
			exception("Erreur dans la transformation d'un document (".$document->_type_element." => $type_element_destination), erreur retournée : ".$retour);
		
		return $nouveau_document_management;
	}
	
    /**
	 *
	 * Vérification des valeurs sur un element
	 *
	**/
	protected function verification_valeurs($element,$champ_a_verifier, $contexte = '') {

        foreach($champ_a_verifier as $champ => $valeur){

            if($element->{$champ} != $valeur) {
				
				$erreur = "Le champ " . $champ . " a pour valeur : " . $element->{$champ} . " alors qu'il devrait etre a " . $valeur.'.';
				
				if(!empty($contexte)) {
					
					$erreur .= ' ----- Contexte : '.$contexte;
				}
				
                exception($erreur);
            }
        }

        return true;
	}
	
}