<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Models\Rapport_parametre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre;

use App\Eden\Managements\Rapports\Rapport_base_management;


/**
* Gestion des rapports
*/
class Rapport_indicateur_management extends Rapport_base_management {

	/**
	 *
	 * On initialise les données du rapport
	 *
	 */
	function __construct($id_rapport = '', $titre = '', $sous_titre = '') {

		parent::__construct($id_rapport, $titre, $sous_titre);

		$this->vue_standard = 'rapport_indicateur';
		$this->valeur = null;
		$this->objectif_atteint = 0;
		$this->objectif = null;
	}

	/**
	 *
	 * Récupère les données pour la vue
	 *
	 */
	public function parametres_pour_vue($ajax = false) {

		parent::parametres_pour_vue($ajax);

        $this->parametres_pour_vue['lien'] = false;
		
		if(!empty($this->rapport_libre))
			$this->parametres_pour_vue['liste_libre'] = Liste_libre::where('type_element', $this->rapport_libre->type_element)->first();

        $this->parametres_pour_vue['lien'] = "";

        if($this->rapport_libre->lien_rapport == 'liste')
            $this->parametres_pour_vue['lien'] = route('base_eden.rapport.liste_avec_indicateur', [$this->rapport_libre->id_rapport]);
        else if($this->rapport_libre->lien_rapport == 'rapport' && !empty($this->rapport_libre->id_rapport_cible))
            $this->parametres_pour_vue['lien'] = route('base_eden.rapport.index', [$this->rapport_libre->id_rapport_cible]);

        if(request()->has('filtres_pour_fiche') && !empty($this->parametres_pour_vue['lien']))
            $this->parametres_pour_vue['lien'].='?filtres_pour_fiche='.base64_encode(serialize(request()->get('filtres_pour_fiche')));

		if(!empty($this->parametrage_rapport_libre)) {
			
			$this->parametres_pour_vue['objectif'] = $this->objectif;
			$this->parametres_pour_vue['objectif_atteint'] = null;

			if($this->parametres_pour_vue['objectif'] !== null && isset($this->parametrage_rapport_libre['reussi_si_inferieure'])) {
				
				if($this->valeur < $this->parametres_pour_vue['objectif'])
					$this->parametres_pour_vue['objectif_atteint'] = $this->parametrage_rapport_libre['reussi_si_inferieure'] ? 1 : 0;
				else
					$this->parametres_pour_vue['objectif_atteint'] = $this->parametrage_rapport_libre['reussi_si_inferieure'] ? 0 : 1;
			}

			if(empty($this->parametrage_rapport_libre['format_affichage'])){

				$this->parametres_pour_vue['valeur_indicateur'] = $this->valeur;
			}
			elseif($this->parametrage_rapport_libre['format_affichage'] == 'montant'){

				$this->parametres_pour_vue['valeur_indicateur'] = montant($this->valeur);
				$this->parametres_pour_vue['objectif'] = $this->parametres_pour_vue['objectif'] != null ? 
					montant($this->parametres_pour_vue['objectif']) : null;
			}
			elseif($this->parametrage_rapport_libre['format_affichage'] == 'montant_lisible'){

				$this->parametres_pour_vue['valeur_indicateur'] = montant_lisible($this->valeur, 2, ',', ' ', 22);
				$this->parametres_pour_vue['objectif'] = $this->parametres_pour_vue['objectif'] != null ? 
					montant_lisible($this->parametres_pour_vue['objectif'], 2, ',', ' ', 22) : null;
			}
            elseif($this->parametrage_rapport_libre['format_affichage'] == 'montant_sans_decimal'){

				$this->parametres_pour_vue['valeur_indicateur'] = montant($this->valeur, 0);
				$this->parametres_pour_vue['objectif'] = $this->parametres_pour_vue['objectif'] != null ? 
					montant($this->parametres_pour_vue['objectif'], 0) : null;
			}

			if(!empty($this->parametrage_rapport_libre['unites']))
				$this->parametres_pour_vue['valeur_indicateur'] .= ' <span style="font-size: 22px;">'.$this->parametrage_rapport_libre['unites'].'</span>';

			if(!empty($this->parametrage_rapport_libre['unites']) && $this->parametres_pour_vue['objectif'] !== null)
				$this->parametres_pour_vue['objectif'] .= ' '.$this->parametrage_rapport_libre['unites'];
		}
		else {

			$this->parametres_pour_vue['valeur_indicateur'] = $this->valeur;
			$this->parametres_pour_vue['objectif_atteint'] = $this->objectif_atteint;
			$this->parametres_pour_vue['objectif'] = $this->objectif;
		}

		// le rapport cible et l'icone
		if(!empty($this->rapport_libre)) {
			
			$this->parametres_pour_vue['icone_dans_rapport'] = $this->rapport_libre->icone_dans_rapport;
			$this->parametres_pour_vue['id_rapport_cible'] = $this->rapport_libre->id_rapport_cible;
		}

        $this->parametres_pour_vue['props_composant'] = [
            'id_rapport' => $this->id_rapport,
            'icone_dans_rapport' => $this->parametres_pour_vue['icone_dans_rapport'],
            'valeur_indicateur' => $this->parametres_pour_vue['valeur_indicateur'],
            'objectif' => $this->parametres_pour_vue['objectif'],
            'objectif_atteint' => $this->parametres_pour_vue['objectif_atteint'],
            'titre_rapport' => traduction($this->rapport_libre->index_traduction . '.titre'),
            'unite' => $this->parametrage_rapport_libre['unites'] ?? '',
            'lien' => $this->parametres_pour_vue['lien'],
        ];

	}

	/**
	 *
	 * Pour les rapports paramétrables
	 *
	 */
    public function genere($ajax = false) {
        // on va chercher la valeur de l'indicateur
		$this->calcule_valeur();
		$this->calcule_valeur(true);
        $type_element = $this->rapport_libre->type_element;
        $management_element = management($type_element);
        $this->applique_filtres($management_element);
        $this->recupere_valeurs_filtres($management_element);
        $this->parametres_pour_vue($ajax);

        return parent::genere($ajax);
    }

	/**
	 *
	 * Pour les rapports paramétrables
	 *
	 */
    public function genere_dans_bloc($ajax = false) {

    	$this->vue_standard = 'rapport_indicateur_dans_bloc';

		$this->calcule_valeur();
		$this->calcule_valeur(true);

		if(!isset($this->valeur))
			$this->valeur = 'n/a';

    	return parent::genere($ajax);
    }

	/**
	 *
	 * Retourne la valeur de l'indicateur ou de l'objectif si le paramètre $objectif est passé à true
	 *
	 */
	protected function calcule_valeur($objectif = false) {

		$nom_champ_type_calcul = $objectif ? 'type_calcul_objectif' : 'type_calcul';
		$nom_champ_champ_calcul = $objectif ? 'champ_calcul_objectif' : 'champ_calcul';
		$nom_champ_sql = $objectif ? 'sql_objectif' : 'sql';
		$nom_attribut_classe = $objectif ? 'objectif' : 'valeur';

		// la valeur a déjà été calculée
		if($this->{$nom_attribut_classe} !== null || empty($this->parametrage_rapport_libre[$nom_champ_type_calcul]))
			return;

		$formulaire = request()->all();
		$type_element = $this->rapport_libre->type_element;
        $management_element = management($type_element);

			
		
		// Dans le cas de l'objectif, on peut avoir une valeur fixe comme valeur
		if(isset($this->parametrage_rapport_libre[$nom_champ_type_calcul]) && $this->parametrage_rapport_libre[$nom_champ_type_calcul] === 'fixe'){

			$this->{$nom_attribut_classe} = $this->parametrage_rapport_libre['valeur_objectif'];
			return;
		}

		if(isset($this->parametrage_rapport_libre[$nom_champ_type_calcul]) && $this->parametrage_rapport_libre[$nom_champ_type_calcul] == 'sql') {
			
			// on doit éventuellement modifier la requete SQL pour y inclure les filtres
			
			$requete = $this->parametrage_rapport_libre[$nom_champ_sql];

            // on applique les différents filtres génériques
            if(!empty($formulaire['filtres_pour_fiche']))
                $this->applique_filtres_pour_fiche_sql($formulaire['filtres_pour_fiche'], $management_element, $type_element, $requete);
            else {
                $requete = str_replace('#filtre_entites#', '', $requete);
                $requete = str_replace('#filtre_utilisateurs#', '', $requete);
                $requete = str_replace('#filtre_dates#', '', $requete);
            }

			list($resultat) = \DB::select($requete);

			$this->{$nom_attribut_classe} = round($resultat->resultat, 2);

			return;
		}

		$requete = modele($type_element);

        if(empty(moi()) && !empty(moi_extranet()))
			$requete = $requete->avec_filtre_extranet();

		// on applique les filtres
		$champs_libres = Champ_libre::where('type_element', $type_element)->get();


        if(isset($formulaire['tableau_de_bord']))
            $this->parametres_pour_vue['tableau_de_bord'] = $formulaire['tableau_de_bord'];

		foreach($champs_libres as $champ_libre) {

			if(empty($this->parametrage_rapport_libre['filtre_applique_'. $type_element . '_' . $champ_libre->nom_sql]))
				continue;

			// on applique le filtre
			$filtre = $this->parametrage_rapport_libre['filtre_applique_'. $type_element . '_' . $champ_libre->nom_sql];
			
			if(isset($this->parametres_filtres) && !empty($this->parametres_filtres)) {

				if(is_array($filtre)) {

					foreach($filtre as $id_filtre => $un_filtre) {

						$filtre[$id_filtre] = str_replace(array_keys($this->parametres_filtres), $this->parametres_filtres, $un_filtre);
					}
				}
			}

			// dans le cas des champs dates, on a forcément une variable
			if(in_array($champ_libre->type, array(4,5)))
				$filtre = array('variable' => $filtre);

            if(in_array($champ_libre->type, array(42,10)))
                $filtre = array($filtre);

            if(in_array($champ_libre->type, array(6))) {
                if(is_array($filtre) && isset($filtre['texte']))
                    $filtre = $filtre['texte'];
                else
                    $filtre = "";
            }
			
			$filtre_def = $filtre;
			
			if(in_array($champ_libre->type, array(1,20))) {
				
				$filtre_def = array();
				
				foreach($filtre as $cle => $valeur) {
					
					if($valeur === "true")
						$filtre_def[] = $cle;
				}
			}

			$requete = $management_element->champ($champ_libre->nom_sql)->applique_filtre_sur_requete($filtre_def, $requete);
		}

        $serie_management = new Serie_management();

        $recherche_avancee = modele('recherche_avancee')
            ->where('type','rapport')
            ->where('id_cible',$this->rapport_libre->id_rapport)
            ->first();

        if(!empty($recherche_avancee)) {

            $filtre = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->structure();
            management('recherche_avancee')->applique_filtrage($filtre,$requete,$type_element);
        }

		// on applique les différents filtres génériques
        if(isset($formulaire['filtres_pour_fiche']))
            $serie_management->applique_filtres_pour_fiche($formulaire['filtres_pour_fiche'], $management_element, $type_element, $requete);

		if(isset($this->parametrage_rapport_libre[$nom_champ_type_calcul]) && $this->parametrage_rapport_libre[$nom_champ_type_calcul] == 'count')
			$valeur_tmp = $requete->count();
		elseif(isset($this->parametrage_rapport_libre[$nom_champ_type_calcul]) && $this->parametrage_rapport_libre[$nom_champ_type_calcul] == 'min')
            $valeur_tmp = $requete->min($this->parametrage_rapport_libre[$nom_champ_champ_calcul]);
		elseif(isset($this->parametrage_rapport_libre[$nom_champ_type_calcul]) && $this->parametrage_rapport_libre[$nom_champ_type_calcul] == 'max')
            $valeur_tmp = $requete->max($this->parametrage_rapport_libre[$nom_champ_champ_calcul]);
		elseif(isset($this->parametrage_rapport_libre[$nom_champ_type_calcul]) && $this->parametrage_rapport_libre[$nom_champ_type_calcul] == 'sum')
            $valeur_tmp = $requete->sum($this->parametrage_rapport_libre[$nom_champ_champ_calcul]);
		elseif(isset($this->parametrage_rapport_libre[$nom_champ_type_calcul]) && $this->parametrage_rapport_libre[$nom_champ_type_calcul] == 'avg')
            $valeur_tmp = $requete->avg($this->parametrage_rapport_libre[$nom_champ_champ_calcul]);

		if(!empty($valeur_tmp))
            $valeur_tmp = round($valeur_tmp, 2);

        $this->{$nom_attribut_classe} = $valeur_tmp;

	}

	/**
	 *
	 * Applique filtre sur les dates pour un indicateurs SQL
	 *
	 */
	protected function applique_filtres_pour_fiche_sql($filtres, $management_element, $type_element, &$requete) {

        $presence_filtre_entite = false;
        $presence_filtre_utilisateur = false;
        $presence_filtre_date = false;
        foreach ($filtres as $colonne => $valeurs) {

            if($colonne == "id")
                continue;

            try {
                $champ = $management_element->champ($colonne);
            } catch (\Exception $e) {
                continue;
            }

            if(in_array($champ->modele->type, [4,5,18])) {
                if(!empty($valeurs['variable'])) {
                    list($debut, $fin) = $champ->transforme_variable_date($valeurs['variable']);
                    if (!empty($debut))
                        $valeurs['debut'] = date('Y-m-d', strtotime($debut));
                    if (!empty($fin))
                        $valeurs['fin'] = date('Y-m-d', strtotime($fin));
                }

                if(!empty($valeurs['debut']) && !empty($valeurs['fin']))
                    $requete = str_replace('#filtre_dates#', " and $type_element.$colonne >= '".$valeurs['debut']."' and $type_element.$colonne <= '".$valeurs['fin']."' ", $requete);
                else if(!empty($valeurs['date_debut']))
                    $requete = str_replace('#filtre_dates#', " and $type_element.$colonne >= '".$valeurs['debut']."' ", $requete);
                else
                    $requete = str_replace('#filtre_dates#', " and $type_element.$colonne <= '".$valeurs['fin']."' ", $requete);

                $presence_filtre_date = true;
            } else if($champ->modele->type == 42) {
                if($champ->modele->type_element_ajax == 'utilisateur') {
                    $requete = str_replace('#filtre_utilisateurs#', ' and ' . $type_element . '.' . $colonne . ' IN (' . implode(',', $valeurs) . ')', $requete);
                    $presence_filtre_utilisateur = true;
                }
                if($champ->modele->type_element_ajax == 'entite') {
                    $requete = str_replace('#filtre_entites#', ' and ' . $type_element . '.' . $colonne . ' IN (' . implode(',', $valeurs) . ')', $requete);
                    $presence_filtre_entite = true;
                }
            } else if($champ->modele->type == 20 && $champ->modele->liste_choix == 1) {
                $requete = str_replace('#filtre_utilisateurs#', ' and ' . $type_element . '.' . $colonne . ' IN (' . implode(',', $valeurs) . ')', $requete);
                $presence_filtre_utilisateur = true;
            }

        }

        if(!$presence_filtre_entite)
            $requete = str_replace('#filtre_entites#', '', $requete);

        if(!$presence_filtre_utilisateur)
            $requete = str_replace('#filtre_utilisateurs#', '', $requete);

        if(!$presence_filtre_date)
            $requete = str_replace('#filtre_dates#', '', $requete);

	}
}
