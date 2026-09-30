<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Models\Rapport_parametre;
use App\Eden\Rapports_libres;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Rapport_favoris;
use App\Eden\Models\Liste_libre;

use App\Eden\Managements\Rapports\Filtres_et_options\Export_excel;
use App\Eden\Managements\Rapports\Filtres_et_options\Dates_mensuelles;
use App\Eden\Managements\Rapports\Filtres_et_options\Dates_quotidiennes;
use App\Eden\Managements\Rapports\Filtres_et_options\Date_quotidienne;
use App\Eden\Managements\Rapports\Filtres_et_options\Utilisateurs;
use App\Eden\Managements\Rapports\Filtres_et_options\Categories;
use App\Eden\Managements\Rapports\Filtres_et_options\Entites;
use App\Eden\Managements\Rapports\Filtres_et_options\Nombre_utilisation;
use App\Eden\Managements\Rapports\Filtres_et_options\Entite;
use App\Eden\Managements\Rapports\Filtres_et_options\Entrepot;
use App\Eden\Managements\Rapports\Filtres_et_options\Periodicite;
use App\Eden\Managements\Rapports\Filtres_et_options\Enregistrer;
use App\Eden\Managements\Rapports\Filtres_et_options\Export_pdf;
use App\Eden\Managements\Rapports\Filtres_et_options\Ca_genere_fourchette;
use App\Eden\Managements\Rapports\Filtres_et_options\Valeur_fourchette;
use App\Eden\Managements\Rapports\Filtres_et_options\Familles_de_produits;
use App\Eden\Managements\Rapports\Filtres_et_options\Champ;
use App\Eden\Managements\Rapports\Filtres_et_options\Valides;
use App\Eden\Managements\Rapports\Filtres_et_options\Fournisseur;
use App\Eden\Managements\Rapports\Filtres_et_options\Client;
use App\Eden\Managements\Rapports\Filtres_et_options\Aide;
use App\Eden\Managements\Rapports\Filtres_et_options\Stock_actuel;
use App\Eden\Managements\Rapports\Filtres_et_options\Recherche;
use App\Eden\Managements\Rapports\Filtres_et_options\Types_utilisateurs;
use App\Eden\Managements\Rapports\Filtres_et_options\Equipe;
use App\Eden\Managements\Rapports\Filtres_et_options\Profils_utilisateurs;
use App\Eden\Managements\Rapports\Filtres_et_options\Connexion_autorisee;
use App\Eden\Managements\Rapports\Filtres_et_options\Ajouter_ligne_budget;
use App\Eden\Managements\Rapports\Filtres_et_options\Suppression_ligne_budget;
use App\Eden\Managements\Rapports\Filtres_et_options\Edition_ligne_budget;
use App\Eden\Managements\Rapports\Filtres_et_options\Projets;
use App\Eden\Managements\Rapports\Filtres_et_options\Pagination;


/**
* Gestion des rapports
*/
class Rapports_management {

	/**
	 *
	 * Retourne une instance de management pour le rapport $id_rapport
	 *
	 */
	public static function instancie_management($id_rapport) {
		// on va chercher les infos du rapport
		$rapport_libre = Rapport_libre::where('id_rapport', $id_rapport)->first();
		$rapport = false;

		$rapport_modifiable = false;

		if($rapport_libre !== null) {

			if($rapport_libre->type_rapport !== null) {

				// on doit instancier une classe standard
				if($rapport_libre->type_rapport == 'histogramme')
					$rapport = new Rapport_histogramme_management($id_rapport);
				elseif($rapport_libre->type_rapport == 'courbe')
					$rapport = new Rapport_courbe_management($id_rapport);
                elseif(in_array($rapport_libre->type_rapport, ['diagramme_circulaire', 'camembert']))
					$rapport = new Rapport_diagramme_circulaire_management($id_rapport);
				elseif($rapport_libre->type_rapport == 'tableau')
					$rapport = new Rapport_tableau_management($id_rapport);
				elseif($rapport_libre->type_rapport == 'pdf')
					$rapport = new Rapport_pdf_management($id_rapport);
				elseif($rapport_libre->type_rapport == 'indicateur')
					$rapport = new Rapport_indicateur_management($id_rapport);
                elseif($rapport_libre->type_rapport == 'graphique_funnel')
                    $rapport = new Rapport_graphique_funnel_management($id_rapport);
                elseif($rapport_libre->type_rapport == 'carte')
                    $rapport = new Rapport_carte_management($id_rapport);

				$rapport_modifiable = true;
			}
		}

		if($rapport === false)
			$rapport = rapport($id_rapport,false);

		return array($rapport, $rapport_modifiable);
	}

	/**
	 *
	 * Récupère la liste des rapports disponibles pour l'utilisateur connecté
	 *
	 */
	public static function rapports_disponibles() {

		$categories = Rapports_libres::categories();

		if(!empty(moi()))
			$rapports_favoris = Rapport_favoris::where('utilisateur_id', moi()->id)->get()->pluck('id_rapport')->toArray();
		else if(!empty(moi_extranet()))
			$rapports_favoris = Rapport_favoris::where('utilisateur_id', moi_extranet()->id)->get()->pluck('id_rapport')->toArray();

		$tableaux_de_bord = modele('tableau_de_bord')->get();

		foreach($categories as $cle => $infos) {



			$rapports = Rapport_libre::where('categorie', $cle)
                ->where(function($r){ $r->where('inactif', 0)->orWhereNull('inactif'); })
                ->orderBy('ordre')->get();


			// on regarde s'il n'y a pas de doublon dans les ordres
			$ordres = array();
			$nouvel_ordre = 1000;

			foreach($rapports as $rapport) {

				if(!isset($ordres[$rapport->ordre])) {

					$ordres[$rapport->ordre] = true;
					continue;
				}

				// on doit lui affecter un nouvel ordre
				$rapport->ordre = $nouvel_ordre;
				$rapport->save();

				$nouvel_ordre++;
			}

			$rapports = Rapport_libre::where('categorie', $cle)
                ->where(function($r){ $r->where('inactif', 0)->orWhereNull('inactif'); })
                ->orderBy('ordre')->get();


			foreach($rapports as $index => $rapport) {

				if(empty($rapport->icone))
					$rapport->icone = 'info';

				if(in_array($rapport->id_rapport, $rapports_favoris))
					$rapport->favoris = 1;
				else
					$rapport->favoris = 0;

				if($rapport->favoris == 1)
					$categories['favoris']['rapports'][] = $rapport;
			}


			// on regarde si c'est des listes libres
			foreach($rapports as $index => $rapport) {

				if(Liste_libre::where('id_rapport', $rapport->id_rapport)->count() > 0)
					$rapport->liste_libre = true;
				else
					$rapport->liste_libre = false;


				// Récupère l'indicateur si un management est présent
				$rapport->indicateur = '';

				$classe_rapport = rapport($rapport->id_rapport, false);

				if($classe_rapport !== false && !is_array($classe_rapport) && method_exists($classe_rapport,'indicateur')) {

					$rapport->indicateur = $classe_rapport->indicateur();
				}
			}

			// on ajoute le tableau de bord
			foreach($tableaux_de_bord as $tableau_de_bord) {

				if($tableau_de_bord->categorie != $cle)
					continue;

				$tableau_de_bord->tableau_de_bord = $tableau_de_bord;

				$rapports->push($tableau_de_bord);
			}



			$categories[$cle]['rapports'] = $rapports;
		}

		return $categories;
	}

	/**
	 *
	 * Enregistre les paramètres pour un rapport
	 *
	 * @param string $id_rapport l'identifiant unique du rapport
	 * @param array $parametres un tableau avec les paramètres à enregistrer
	 * @param bool $ajoute si false, le tableau $parametres sera enregistré telquel, si true, il sera mergé avec les paramètres actuels
	 *
	 */
	public static function enregistre_parametres($id_rapport, $parametres, $ajoute = false) {

		if(empty(moi()))
			return true;

		if($ajoute === true) {

			$parametres = array_merge(self::recupere_parametres($id_rapport), $parametres);

            foreach ($parametres as $index => $parametre) {
                if (empty($parametre))
                    unset($parametres[$index]);
            }
		}

		// on efface l'existant
		Rapport_parametre::where('utilisateur_id', moi()->id)->where('id_rapport', $id_rapport)->delete();

		$rapport_parametre = new Rapport_parametre;
		$rapport_parametre->utilisateur_id = moi()->id;

		$rapport_parametre->id_rapport = $id_rapport;
		$rapport_parametre->parametres = base64_encode(serialize($parametres));

		// dump($rapport_parametre->parametres);

		$rapport_parametre->save();

		return true;
	}

	public static function recupere_parametres($id_rapport) {

		if(empty(moi()))
			return array();

		$parametres = Rapport_parametre::where('utilisateur_id', moi()->id)->where('id_rapport', $id_rapport)->first();
		if($parametres !== null)
			return unserialize(base64_decode($parametres->parametres));

		return array();
	}

	/**
	 *
	 * Permet de retoucher les paramètres d'un rapport en spécifique
	 *
	 */
	public static function retouche_parametres($parametres) {

		return $parametres;
	}

	/**
	 *
	 * Crée une bulle d'aide sur le rapport
	 *
	 */
	public function aide($rapport, $texte) {

		return Aide::applique($rapport, $texte);
	}

	/**
	 *
	 * Retourne un fichier pdf
	 */
	public function export_pdf($rapport, $orientation = 'portrait', $format_papier = 'a4') {

		return Export_pdf::applique($rapport, $orientation, $format_papier);
	}

	/**
	 *
	 * Retourne un export d'excel
	 */

	public function export_excel($rapport) {

		return Export_excel::applique($rapport);
	}

    /**
     *
     * Recherche un rapport
     */

    public function recherche($rapport) {

        return Recherche::applique($rapport);
    }

	/**
	 *
	 * Retourne une période de dates pour le rapport
	 *
	 */
	public function dates_mensuelles($rapport) {

		return Dates_mensuelles::applique($rapport);
	}

	/**
	 *
	 * Retourne une période de dates pour le rapport
	 *
	 */
	public function dates_quotidiennes($rapport) {

		return Dates_quotidiennes::applique($rapport);
	}

	/**
	 *
	 * Permet de choisir une date
	 *
	 */
	public function date_quotidienne($rapport) {

		return Date_quotidienne::applique($rapport);
	}

	/**
	 *
	 * Filtre sur une liste d'utilisateurs
	 *
	 */
	public function utilisateurs($rapport) {

		return Utilisateurs::applique($rapport);
	}

	/**
	 *
	 * Filtre sur une liste de categories
	 *
	 */
	public function categories($rapport) {

		return Categories::applique($rapport);
	}

	/**
	 *
	 * Filtre sur un nombre d'utilisation
	 *
	 */
	public function nombre_utilisation($rapport) {

		return Nombre_utilisation::applique($rapport);
	}

	/**
	 *
	 * Filtre sur un nombre d'utilisation
	 *
	 */
	public function ca_genere_fourchette($rapport) {

		return Ca_genere_fourchette::applique($rapport);
	}

	/**
	 *
	 * Filtre sur un nombre d'utilisation
	 *
	 */
	public function valeur_fourchette($rapport) {

		return Valeur_fourchette::applique($rapport);
	}

	/**
	 *
	 * Ajoute un filtre sur les familles de produits
	 *
	 */
	public function familles_de_produits($rapport) {

		return Familles_de_produits::applique($rapport);
	}

	/**
	 *
	 * Filtre sur une liste d'entités
	 *
	 */
	public function entites($rapport) {

		return Entites::applique($rapport);
	}

	/**
	 *
	 * Filtre sur différents champs.
	 * Usage :
	 * rapport = id_rapport
	 * $champs = $this->champs($rapport, [management('element')->champ('nom_sql')]);
	 *
	 */
	public function champs($rapport, $champs) {

		return Champ::applique($rapport, $champs);
	}

	/**
	 *
	 * Filtre les documents validés ou pas
	 * Usage :
	 * rapport = id_rapport
	 * $champs = $this->champs($rapport, [management('element')->champ('nom_sql')]);
	 *
	 */
	public function valides($rapport) {

		return Valides::applique($rapport);
	}

	/**
	 *
	 * Filtre pour une entité unique
	 *
	 */
	public function entite($rapport) {

		return Entite::applique($rapport);
	}

	/**
	 *
	 * Filtre pour un entrepot unique
	 *
	 */
	public function entrepot($rapport) {

		return Entrepot::applique($rapport);
	}

	/**
	 *
	 * Filtre sur une liste d'entités
	 *
	 */
	public function periodicite($rapport, $dates = array()) {

		return Periodicite::applique($rapport, $dates);
	}

	/**
	 *
	 * Filtre par fournisseur
	 *
	 */
	public function fournisseur($rapport) {

		return Fournisseur::applique($rapport);
	}

	/**
	 *
	 * Filtre par client
	 *
	 */
	public function client($rapport) {

		return Client::applique($rapport);
	}

	/**
	 *
	 * Bouton enregistrer sur un rapport
	 *
	 */
	public function enregistrer($rapport) {

		return Enregistrer::applique($rapport);
	}

	/**
	 *
	 * Retourne l'indicateur à afficher. Doit être vide en standard
	 *
	 */
	public function indicateur() {
		return "";
	}

    /**
     *
     * Filtre par types des utilisateurs
     *
     */
    public function types_utilisateurs($rapport) {
        return Types_utilisateurs::applique($rapport);
    }

    /**
     *
     * Filtre par equipe
     *
     */
    public function equipe($rapport) {
        return Equipe::applique($rapport);
    }

    /**
     *
     * Filtre par profil utilisateur
     *
     */
    public function profils_utilisateurs($rapport) {
        return Profils_utilisateurs::applique($rapport);
    }

    /**
     *
     * Filtre sur l'autorisation à la connexion
     *
     */
    public function connexion_autorisee($rapport) {
        return Connexion_autorisee::applique($rapport);
    }

	/**
     *
     * Formulaire d'ajout d'une rubrique ou d'un poste
     *
     */
    public function ajouter_ligne_budget($rapport) {
        return Ajouter_ligne_budget::applique($rapport);
    }

	/**
     *
     * Suppresion des lignes sélectionnées dans le rapport budget
     *
     */
    public function suppression_ligne_budget($rapport) {
        return Suppression_ligne_budget::applique($rapport);
    }

	/**
     *
     * Edition des lignes sélectionnées dans le rapport budget
     *
     */
    public function edition_ligne_budget($rapport) {
        return Edition_ligne_budget::applique($rapport);
    }

	/**
	 *
	 * Ajoute un filtre sur le stock actuel
	 *
	 */
	public function stock_actuel($rapport) {

		return Stock_actuel::applique($rapport);
	}

	/**
	 *
	 * Gestion de la pagination pour les rapports de type liste
	 *
	 */
	public function pagination($rapport, $nombre_total, $nombre_par_page = false) {

		if($nombre_par_page === false)
			$nombre_par_page = fonctionnalite('nombre_de_lignes_dans_listes');

		if(defined('export_excel_en_cours') && export_excel_en_cours === true)
            return Pagination::applique($rapport, $nombre_total, $nombre_total);

        return Pagination::applique($rapport, $nombre_total, $nombre_par_page);
	}

	/**
	 *
	 * Ajoute un filtre sur les projets
	 *
	 */
	public function projets($rapport) {

		return Projets::applique($rapport);
	}

    /**
     * @param $titres array
     * @return array
     *
     * On retourne la ligne à ajouté, utile pour la spécification
     *
     */
    public function recupere_titres_rapport($titres){

        return $titres;

    }

    /**
     * @param $colonnes array
     * @param $element_id int
     * @return array
     *
     * On retourne la ligne à ajouté, utile pour la spécification
     *
     */
    public function recupere_colonnes_ligne($colonnes, $element_id){

        return $colonnes;

    }

    /**
     *
     * On recupère les paramètres pour le front
     *
     */
    public function parametres_pour_vue($ajax = false) {
        return $this->rapport->parametres_pour_vue($ajax);
    }
}
