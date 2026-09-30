<?php

namespace App\Eden\Managements;

use App\Eden\Models\Treso_utilisateurs;
use App\Eden\Managements\Tables_libres_management;
use App\Eden\Managements\Element_management;
use App\Eden\Models\Table_libre;
use DB;

use Log;

/**
 * Gestion des recherches globales sur l'ERP
 */
class Recherche_management {

    /**
     * Fonction renvoyant les donnees nécessaires à l'affichage de la liste des
     * utilisateurs
     *
     * @param string    $recherche    recherche effectuée par l'utilisateur
     *
     * @return Array   retourne la liste des résultats de la recherche
     *                 dans la table index_recherche
     */
    public function recuperer_resultats_recherche($recherche) {

		$retour = array();

		// on initialise le nombre maximum de résultat par type élement recherche
		$resultat_de_recherche_maximum = 50;

		$types_elements = array();
		$resultats_bdd = collect();
		$tables_libres = Table_libre::where('disponible_recherche_rapide', '1')->get();

        $nettoyer_caracteres_speciaux = fn($texte) => preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $texte);

        $recherche_initial = $nettoyer_caracteres_speciaux($recherche);

        preg_match_all('/"(.*?)"/', $recherche, $recherches_exactes);

        foreach($recherches_exactes[0] as $recherche_exacte){
            $recherche = str_replace($recherche_exacte,' ',$recherche);
        }

        $recherches_exactes = $recherches_exactes[1];

        foreach($recherches_exactes as &$recherche_exacte){
            $recherche_exacte = $nettoyer_caracteres_speciaux($recherche_exacte);
        }

		$recherches_non_exactes = array_filter(explode(' ', $nettoyer_caracteres_speciaux($recherche)),fn($mot) => mb_strlen($mot) >= 3);

		foreach($tables_libres as $table_libre) {

			$resultats_temp = modele($table_libre->type_element)
                    ->initie_requete_pour_recherche_globale()
                    ->orderByRaw("MATCH(chaine_tags_recherche) AGAINST ('\"" . $recherche_initial . "\"' In BOOLEAN MODE) DESC");

            if($table_libre->recherche_globale_like == 1){
				$like = str_replace(' ', '%', $recherche_initial);
				$resultats_temp->where("chaine_tags_recherche","LIKE",'%'.$like.'%');
			}
            else {
                if (!empty($recherches_non_exactes))
                    $resultats_temp->whereRaw("MATCH(chaine_tags_recherche) AGAINST (\"+" . implode('* +', $recherches_non_exactes) . "*\" In BOOLEAN MODE)")
                        ->orderByRaw("MATCH(chaine_tags_recherche) AGAINST (\"+" . implode('* +', $recherches_non_exactes) . "*\" In BOOLEAN MODE) DESC");

                foreach ($recherches_exactes as $recherche_exacte_tmp) {
                    $resultats_temp->whereRaw("MATCH(chaine_tags_recherche) AGAINST ('\"" . $recherche_exacte_tmp . "\"')");
                }
            }

            $requete_element = clone $resultats_temp;

            $resultats_temp = modele($table_libre->type_element)->hydrate(select($resultats_temp->take($resultat_de_recherche_maximum)));

			if(count($resultats_temp) == 0)
				continue;

            $champs_libres_multi_selection = champs_libres_multiselection($table_libre->type_element);

            $valeurs_champs_multi_select = [];

            // on va chercher les valeurs pour les multi sélections
            foreach($champs_libres_multi_selection as $champ_libre) {

                $valeurs_champs_multi_select[$champ_libre->nom_sql] = \DB::table($champ_libre->table_pivot)
                    ->whereIn('cle_locale',$requete_element->select('id'))
                    ->get()
                    ->groupBy('cle_locale');

            }

			foreach($resultats_temp as $resultat) {

                // On attribue les valeurs des champs multiselect au bon éléments
                foreach ($valeurs_champs_multi_select as $nom_sql => $valeurs) {
                    if(isset($valeurs[$resultat->id]))
                        $resultat->{$nom_sql} = $valeurs[$resultat->id]->pluck('valeur')->toArray();
                }

				$management = management($table_libre->type_element, $resultat->id, $resultat);

				// c'est pour la perf
				if(!empty($table_libre->affichage_recherche)) {

					$lien = $management->affiche_lien(false, false, true);
				}
				else {

					$lien = $management->affiche_lien();
				}

				$retour[] = [

	                'type_element' => $table_libre->type_element,
	                'nom_element' => ucfirst($table_libre->element),
	                'lien' => $lien,
	                'id_element' => $management->modele->id,
	            ];
			}

            if(strpos($table_libre->type_element,'achat') != false)
			    $types_elements[$table_libre->type_element] = (count($resultats_temp)).' '.$table_libre->element_pluriel . ' achat';

            else if(strpos($table_libre->type_element,'vente') != false)
			    $types_elements[$table_libre->type_element] = (count($resultats_temp)).' '.$table_libre->element_pluriel . ' vente';

            else
			    $types_elements[$table_libre->type_element] = (count($resultats_temp)).' '.$table_libre->element_pluriel;

		}

		return array($retour, $types_elements);
    }

    /**
     * 
     * Fonction qui permet de récupérer des éléments similaires pour éviter les doublons
     * 
     */
    public function dedoublonnage($valeurs_recherches,$type_element){

        $recherches_match = array();
		$soundex = array();

		foreach($valeurs_recherches as $nom_sql => $valeur_recherche){

			if(empty($valeur_recherche))
				continue;

			$modele_champ_libre = champ_libre_modele($type_element, $nom_sql);

			$termes_a_remplacer = array(
				'+','-','<','>','(',')','~','*','"','&','|',':','@',"'"
			);

			$valeur_recherche = str_replace($termes_a_remplacer,' ',$valeur_recherche);

			$recherche = explode(' ',$valeur_recherche);

			$recherches_match = array_merge($recherches_match,$recherche);

			$soundex[] = 'SOUNDEX(`'.$nom_sql.'`) = SOUNDEX("'.$valeur_recherche.'")';
		}

		if(empty($recherches_match))
			return [];

		$requete = modele($type_element)
			->where(function($q) use ($soundex,$recherches_match) {
				$q->whereRaw("MATCH(chaine_tags_recherche) AGAINST ('+".implode('* +',$recherches_match)."*' IN BOOLEAN MODE)");

				$q->orWhere(function($q) use ($soundex) {
					foreach($soundex as $condition_soundex){
						$q->WhereRaw($condition_soundex);
					}
				});
			});

		$requete->orderByRaw("(".implode(') + (',$soundex).") DESC")
			->orderByRaw("MATCH(chaine_tags_recherche) AGAINST ('+".implode('* +',$recherches_match)."*' IN BOOLEAN MODE) DESC");

		foreach($recherches_match as $terme){
			$requete->orderByRaw("MATCH(chaine_tags_recherche) AGAINST ('+".$terme."*' IN BOOLEAN MODE) DESC");
		}

		if($requete->count() > 10)
			return [];

		$resultats = $requete->get();

		$champs_libres_multi_selection = champs_libres_multiselection($type_element);

		$valeurs_champs_multi_select = [];

		// on va chercher les valeurs pour les multi sélections
		foreach($champs_libres_multi_selection as $champ_libre) {

			$valeurs_champs_multi_select[$champ_libre->nom_sql] = \DB::table($champ_libre->table_pivot)
				->whereIn('cle_locale',$requete->select('id'))
				->get()
				->groupBy('cle_locale');

		}

		$table_libre = table_libre($type_element);

		$retour = [];

		foreach($resultats as $resultat) {

			// On attribue les valeurs des champs multiselect au bon éléments
			foreach ($valeurs_champs_multi_select as $nom_sql => $valeurs) {
				if(isset($valeurs[$resultat->id]))
					$resultat->{$nom_sql} = $valeurs[$resultat->id]->pluck('valeur')->toArray();
			}

			$management = management($type_element, $resultat->id, $resultat);

			$lien = '<a href="'.$management->lien_vers_element($resultat->id).'">'.$management->affichage_dedoublonnage().'</a>';

			$retour[] = [
				'lien' => $lien,
				'id_element' => $management->modele->id,
			];
		}

        return $retour;
    }


}
