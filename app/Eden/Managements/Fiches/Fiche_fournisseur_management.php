<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Managements\Listes_management;

/**
 * Gestion des fiches fournisseurs
 */
class Fiche_fournisseur_management extends Fiche_management {

	/**
	 * 
	 * Prépare les données pour la fiche
	 * 
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 * 
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {

		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);
		
		// les thèmes de filtres
		if(fonctionnalite('theme_de_filtres_fournisseur') === true)			
			$donnees['themes_de_filtres'] = $this->themes_de_filtres();
		
		// les paiements
		
		$donnees['paiement_fiche_client'] = management('paiement')->modele_par_defaut();
		$donnees['paiement_fiche_client']->fournisseur_id = $this->id_element;
		
		$donnees['paiement_fiche_fournisseur'] = management('paiement')->modele_par_defaut();
		$donnees['paiement_fiche_fournisseur']->fournisseur_id = $this->id_element;

        $modules = $this->modules_utilises();

        // on va ajouter les abonnements
        if(in_array('indicateurs', $modules)) {

            $donnees['indicateurs'] = $this->indicateurs();
        }

		return $donnees;
	}

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $index_suppression = array_search('suppression', array_column($options_fil_ariane, 'id'));

        if($index_suppression !== false)
            unset($options_fil_ariane[$index_suppression]);

        $options_fil_ariane[] = [
            'id' => 'modale_suppression_element',
            'parametres' => ['type_element_options' => 'fournisseur', 'include_depuis_fiche' => true],
            'ordre' => 0,
			'option_a_droite' => true,
        ];

        $options_fil_ariane[] = [
            'id' => 'stocks',
            'ordre' => -3
        ];

        $options_fil_ariane[] = [
            'id' => 'ajout_document',
            'ordre' => -1
        ];

        $options_fil_ariane[] = [
            'id' => 'impression_pdf',
            'ordre' => 0
        ];

        return $options_fil_ariane;
    }
	
	/**
	 * 
	 * Retourne les contacts liés au fournisseur
	 * 
	 * @return collection
	 * 
	 */
	public function contacts() {

        $contacts = management('contact')->recuperer_contact_element('fournisseur', $this->id_element,array('npai'));

		if($contacts === null)
			return collect(array());
		
		return collect($contacts);
	}
	
	/**
	 * 
	 * Retourne les filtres liés à l'article
	 * 
	 * Il faut retourner les filtres disponibles en fonction de la catégorie de l'article, et les filtres qui ont été sélectionnés
	 * 
	 * @return collection
	 * 
	 */
	public function themes_de_filtres() {
		
		$filtres_disponibles = array();
		
		$fournisseur = modele('fournisseur', $this->id_element);
		
		// quels thèmes de filtres pour la catégorie de l'article ?
		$themes_de_filtres = modele('theme_de_filtres')->get();
		
		
		foreach($themes_de_filtres as $theme_de_filtres) {
			
			// on va chercher les filtres disponibles
			$filtres_dispo = modele('theme_de_filtres')->find($theme_de_filtres->id)->filtres;
			
			if(!empty($filtres_dispo))
				$filtres_dispo = $filtres_dispo->pluck('filtre', 'id');
			
			// on va chercher les filtres sélectionnés
			$filtres_choisis = modele('fournisseur')->find($this->id_element)->filtres;
			
			if(!empty($filtres_choisis))
				$filtres_choisis = $filtres_choisis->pluck('filtre', 'id');
			
			$filtres_disponibles[] = array(
				
				'theme_de_filtres' => $theme_de_filtres,
				'filtres_dispo' => $filtres_dispo,
				'filtres_choisis' => $filtres_choisis,
			);
		}
		
		if($filtres_disponibles === null)
			return collect(array());
		
		return collect($filtres_disponibles);
	}

    /**
     *
     * Envoie les blocs que l'on peut imprimer sur la fiche
     *
     */
    public function blocs_impression_fiche(){

        return array(
            'informations_principales' => array(
                'nom' => "Informations principales :",
                'blocs' => array(
                    'bloc_fournisseur' => 'Généralités',
                    'bloc_contact' => 'Contacts',
                    'bloc_adresse' => 'Adresses',
                    'bloc_article_fournisseur' => 'Articles du fournisseur',
                ),
            ),
        );
    }



    /**
     *
     * On ajoute le CA, l'encours et le montant des devis du client
     *
     */
    public function indicateurs() {

        $factures_chiffre_affaire = modele('facture_achat')
            ->where('fournisseur_id', $this->id_element)
            ->where('date', '>', date('Y-m-d', strtotime('-1 year')))
            ->sum('montant_document_ht');

        $avoirs_chiffre_affaire = modele('avoir_achat')
            ->where('fournisseur_id', $this->id_element)
            ->where('date', '>', date('Y-m-d', strtotime('-1 year')))
            ->sum('montant_document_ht');

        $chiffre_affaire = $factures_chiffre_affaire - $avoirs_chiffre_affaire;

        $factures_encours = modele('facture_achat')
            ->where('fournisseur_id', $this->id_element)
            ->zero_ou_null('regle')
            ->where('valide', 1)
            ->sum('montant_document_ht');

        $avoirs_encours = modele('avoir_achat')
            ->where('fournisseur_id', $this->id_element)
            ->zero_ou_null('regle')
            ->where('valide', 1)
            ->sum('montant_document_ht');

        $encours = $factures_encours - $avoirs_encours;

        $montant_devis = modele('devis_achat')
            ->where('fournisseur_id', $this->id_element)
            ->where('date', '>', date('Y-m-d', strtotime('-1 year')))
            ->sum('montant_document_ht');

        return array('chiffre_affaire' => $chiffre_affaire, 'encours' => $encours, 'montant_devis' => $montant_devis);
    }
	
}
