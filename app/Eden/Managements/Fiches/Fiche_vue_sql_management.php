<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Managements\Listes_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use DB;
use App\Eden\Variables;

/**
 * Gestion des fiches clients
 */
class Fiche_vue_sql_management extends Fiche_management {
		
	/**
	 *
	 * Prépare les données pour la fiche
	 *
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 *
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {

		// on prépare la liste des factures
		$liste_management = new Listes_management();
		
		// on récupère les modules utilisés pour optimiser
		$modules = $this->modules_utilises();

        // on va chercher les données de base
        $donnees = parent::prepare_donnees_pour_fiche($donnees);

        temps_execution('Fiche: après prepare_donnees_pour_fiche (parent)');

		// on va ajouter la gestion_champs_libres
		if(in_array('gestion_champs_libres', $modules)) {

			$donnees['gestion_champs_libres'] = $this->gestion_champs_libres();
		}

		return $donnees;
	}
	
	/**
	 *
	 * Retourne la gestion_champs_libres
	 *
	 * @return array
	 *
	 */
	public function gestion_champs_libres() {

        $vue_sql_management = management('vue_sql', $this->id_element);
        $vue_sql_modele = $vue_sql_management->modele;

        $gestion_champs_libres = array();

        if($vue_sql_modele->type_de_vue == 0) {
            $tables_vue_sql = explode(',', $vue_sql_modele->tables);

            foreach ($tables_vue_sql as $type_element) {

                $table_libre = Table_libre::where('type_element', $type_element)->first();

                $gestion_champs_libres['type_element'][$type_element] = $table_libre->nom_table;

                $champs_libres = Champ_libre::where('type_element', $type_element)->select('id_cl','type_element','nom_sql','nom')->orderby('nom')->get();

                foreach ($champs_libres as $champ_libre) {

                    $actif = false;

                    if (Champ_libre::where('type_element', $vue_sql_modele->nom_sql)
                        ->where('type_element_origine', $type_element)
                        ->where('nom_sql_origine', $champ_libre->nom_sql)
                        ->first()) {

                        $actif = true;
                    }
                    $gestion_champs_libres[$type_element][] = array(
                        'actif' => $actif,
                        'modele' => $champ_libre,
                    );

                }
            }
        }
        else{

            $gestion_champs_libres = Champ_libre::where('type_element',$vue_sql_modele->nom_sql)->select('id_cl','nom_sql','nom',DB::raw("CONCAT(type_element_origine,'.',nom_sql_origine) AS parent"))->get()->toArray();

        }

		return $gestion_champs_libres;
	}
	

}
