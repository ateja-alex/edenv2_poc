<?php

namespace App\Eden;


/**
 *
 * Liste des rapports standards
 *
 */
class Rapports_libres {
	
	/**
	 * 
	 * Récupère la liste des rapports et vérifie leur présence en base
	 * 
	 */
    public static function rapports_defaut() {
		
		$rapports = [];
		
		// on vient ajouter les rapports standards
		$repertoire = scandir(app_path('Eden/Migrations/Rapports'));
		
		foreach($repertoire as $fichier) {
			
			if($fichier == '.' || $fichier == '..')
				continue;
			
			$contenu_tmp = require(app_path('Eden/Migrations/Rapports/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);
			
			$rapports[$index_tableau] = $contenu_tmp;
		}
		
		// on vient ajouter les tables libres spécifiques
		if(is_dir(app_path('Migrations/Rapports'))) {
			
			$repertoire = scandir(app_path('Migrations/Rapports'));
			
			foreach($repertoire as $fichier) {
				
				if($fichier == '.' || $fichier == '..')
					continue;
				
				$contenu_tmp = require(app_path('Migrations/Rapports/'.$fichier));
				
				$index_tableau = str_replace('.php', '', $fichier);
				
				$rapports[$index_tableau] = $contenu_tmp;
			}
		}
		
		return $rapports;
	}

    /**
	 *
	 * Récupère la liste des rapports standard
	 *
	 */
    public static function rapports_standard() {

		$rapports = [];

		// on vient ajouter les rapports standards
		$repertoire = scandir(app_path('Eden/Migrations/Rapports'));

		foreach($repertoire as $fichier) {

			if($fichier == '.' || $fichier == '..')
				continue;

			$index_tableau = str_replace('.php', '', $fichier);

			$rapports[] = $index_tableau;
		}

		return $rapports;
	}
	
	
	/**
	 * 
	 * Retourne la liste des catégories standards pour les rapports
	 * 
	 */
    public static function categories_index() {
		
		return array(
			
			'favoris' => 'interface.categories_rapport.favoris',
			'gestion_commerciale' => 'interface.categories_rapport.gestion_commerciale',
			'gestion_projet' => 'interface.categories_rapport.gestion_projet',
			'crm' => 'interface.categories_rapport.crm',
			'rh' => 'interface.categories_rapport.rh',
			'activite_operationnelle' => 'interface.categories_rapport.activite_operationnelle',
			'animation_reseau' => 'interface.categories_rapport.animation_reseau',
			'process_achat' => 'interface.categories_rapport.process_achat',
			'production_livraison' => 'interface.categories_rapport.production_livraison',
			'data' => 'interface.categories_rapport.data',
			'gestion' => 'interface.categories_rapport.gestion',
			'compta' => 'interface.categories_rapport.compta',
			'administration' => 'interface.categories_rapport.administration',
            'extranet' => 'interface.categories_rapport.extranet',
		);
    }

    /**
     *
     * Retourne la liste des catégories standards pour les rapports
     *
     */
    public static function categories() {

        $categories = array();

        $categories_tmp = modele('categorie_rapport')->select('*', \DB::raw('COALESCE(ordre, 0) as ordre'))->orderBy('ordre')->get();

        foreach($categories_tmp as $categorie){

            $categories[$categorie->index] = array('nom' => traduction("categorie_rapport.{$categorie->id}.nom"), 'index_traduction' => "categorie_rapport.{$categorie->id}.nom");
        }

        return $categories;
    }

    /**
     *
     * Récupération des colonnes standard des rapports
     *
     */
    public static function rapports_colonnes_et_calculs_standard(){

        $informations_par_liste = [];
        $colonnes_par_liste = [];
        $calculs_par_liste = [];

		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Rapports'));

		foreach($repertoire as $fichier) {

			if($fichier == '.' || $fichier == '..')
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/Rapports/'.$fichier));

            if(!isset($contenu_tmp['liste_libre']))
				continue;

			$index_tableau = str_replace('.php', '', $fichier);

            $colonnes_par_liste[$index_tableau] =  $contenu_tmp['liste_libre']['colonnes'];

            if(!empty($contenu_tmp['liste_libre']['calculs']))
                $calculs_par_liste[$index_tableau] =  $contenu_tmp['liste_libre']['calculs'];
        }

        foreach($colonnes_par_liste as $id_rapport => $colonnes){

            $colonnes_formatees = [];

            foreach($colonnes as $colonne){

                $nom_colonne = $colonne['nom'];

                if($nom_colonne == '#')
                    continue;

                $type = !empty($colonne['type']) ? $colonne['type'] : "standard";

                if(!empty($colonne['methode']))
                    $type = 'methode';

                else if(!empty($colonne['champ']))
                    $type = 'champ';

                $nom_colonne = strtolower(retraite_caracteres_speciaux($nom_colonne,'_'));

                $index_traduction = 'rapport.' . $id_rapport . '.colonne.' . $nom_colonne;

                if($type == 'standard' || $type == 'liaison') {
                    $colonnes_formatees['type_1_' . $colonne['valeur']] = $index_traduction;
                }
                else if($type == 'methode')
                    $colonnes_formatees['type_2_'.$colonne['methode']] = $index_traduction;
                else if($type == 'champ')
                    $colonnes_formatees['type_3_'.$colonne['champ']] = $index_traduction;

            }

            $informations_par_liste[$id_rapport]['colonnes'] = $colonnes_formatees;
        }

        foreach($calculs_par_liste as $id_rapport => $calculs){

            foreach($calculs as $calcul){

                $nom_calcul = $calcul['nom'];

                $nom_calcul = strtolower(retraite_caracteres_speciaux($nom_calcul,'_'));

                $index_traduction = 'rapport.' . $id_rapport . '.calcul.' . $nom_calcul;

                $identifiant ='';

                if(!empty($calcul['nom_sql']))
                    $identifiant .= $calcul['nom_sql'].'_';

                if(!empty($calcul['type_calcul']))
                    $identifiant .= $calcul['type_calcul'].'_';

                if(!empty($calcul['unite']))
                    $identifiant .= $calcul['unite'].'_';

                if(!empty($calcul['split']))
                    $identifiant .= $calcul['split'].'_';

                if(!empty($calcul['top']))
                    $identifiant .= $calcul['top'];

                $calculs_formatees[$identifiant] = $index_traduction;
            }

            $informations_par_liste[$id_rapport]['calculs'] = $calculs_formatees;
        }

        return $informations_par_liste;
    }
	
}






