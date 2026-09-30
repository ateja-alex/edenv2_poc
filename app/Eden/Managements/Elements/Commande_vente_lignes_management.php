<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Liste_libre;

class Commande_vente_lignes_management extends Document_lignes_management {
	
	/**
	 *
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);
		
		// on met à jour les stocks réservés
		$this->mise_a_jour_stocks(false);
	}

    public function enregistre($modifications = array(), $modele = false){

        if((empty($this->modele) || $this->modele->transforme_fournisseur === null) && !isset($modifications['transforme_fournisseur'])) {

			$modifications['transforme_fournisseur'] = 0;
			$modifications['transforme_reliquat_commande_fournisseur'] = 0;
			$modifications['transforme_livraison'] = 0;
			$modifications['transforme_reliquat_reception_fournisseur'] = 0;
		}

        return parent::enregistre($modifications, $modele);
    }


    /**
	 * 
	 * On crée les lignes à réceptionner pour les fournisseurs
	 * 
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		// on met à jour les stocks réservés
		// on fait un test pour voir s'il est nécessaire ou non de mettre à jour les stocks réservés, car c'est un truc qui prend assez de temps
		$mise_a_jour_necessaire = false;

        $recreation = empty($modele['document_annule']);

		$champs_a_tester = array(

            'document_annule',
			'quantite',
			'article_id',
			'transforme',
			'transforme_reliquat',
			'entrepot_id',
		);

		foreach($champs_a_tester as $champ) {

			if(empty($modele_avant))
				$mise_a_jour_necessaire = true;

			if($modele_avant->{$champ} != $modele->{$champ})
				$mise_a_jour_necessaire = true;
		}

		if($mise_a_jour_necessaire === true)
			$this->mise_a_jour_stocks($recreation);
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'zoom';

        return $liste_options;
    }

    /**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element, $type_element,$id_liste_parent) {

        $liste_libre = Liste_libre::where('type_element','commande_vente_lignes')
            ->where('id_rapport','detail_ligne_commande_vente_lignes')->first();

		// On va chercher les infos qui nous interessent
		$management = management($type_element, $id_element);

		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_ligne_stocks";

        $nombre_de_conditionnement = modele('commande_vente_lignes')
                ->where('nomenclature_ligne_parent',$id_element)
                ->count();
        $liste_management = liste('commande_vente_lignes','detail_ligne_commande_vente_lignes');

        $filtres_pour_fiche = array(
            'nomenclature_ligne_parent' => $id_element
        );

        $donnees_liste = $liste_management->recupere_liste($liste_libre->id,[
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'nombre_par_pages' => $nombre_de_conditionnement,
            'id_liste_parent' => $id_liste_parent,
        ]);

        $donnees_liste['lignes_selectionnees'] = array();
        $donnees_liste['desactiver_checkbox'] = true;

		$vue_render = view($vue, array(
			'management' => $management,
			'id_liste' => $liste_libre->id,
			'type_element' => 'commande_vente_lignes',
			'id_element' => $id_element,
			'liste_libre' => $liste_libre,
			'donnees_liste' => $donnees_liste,
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'id_liste_parent' => $id_liste_parent,
		))->render();

		return array('composant' => $vue_render);
	}
}