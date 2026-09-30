<?php

namespace App\Eden\Managements\Elements;

class Maintenance_intervention_management extends Element_management {

	/**
	 * 
	 * On ajoute l'option pour facturer
	 * 
	 */
	public function colonne_options($modele, $mode_corbeille = false, $options = array(), $id_liste = false) {
	
		// on ajoute l'option pour générer la facture
		if(empty($modele->facture_vente_id))
			$options['generer_facture'] = '<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste js_generer_facture_maintenance_intervention" data-toggle="tooltip" title="Générer la facture" id_element="'.$modele->id.'"><span class="fa fa-file-invoice"></span></span>';
		
		return parent::colonne_options($modele, $mode_corbeille, $options,$id_liste);
	}
	
	/**
	 * 
	 * On génère la facture pour l'intervention
	 * 
	 */
	public function generer_facture() {
	
		// on va chercher la maintenance
		$maintenance = management('maintenance', $this->modele->maintenance_id);
		
		if(empty($maintenance->modele->article_id))
			return "Il n'est pas possible de générer cette facture car aucun article n'est défini sur le contrat de maintenance";
		
		// ok on génère la facture
		$facture = management('facture_vente');
		
		$article = management('article', $maintenance->modele->article_id);
		
		$tarif = $article->modele->tarif;
		
		if(!empty($maintenance->modele->tarif))
			$tarif = $maintenance->modele->tarif;
		
		$infos = array(
		
			'date' => date('Y-m-d'),
			'objet' => 'Facturation contrat de maintenance',
			'client_id' => $maintenance->modele->client_id,
			'articles' => array(
				
				array(
				
					'article_id' => $maintenance->modele->article_id,
					'designation' => $article->modele->designation,
					'quantite' => 1,
					'tarif' => $tarif,
					'remise' => 0,
					'tva' => $article->modele->taux_de_tva,
				),
			),
		);
		
		$retour = $facture->enregistre($infos);
		
		if($retour !== true)
			return $retour;
		
		// ok la facture a été éditée, on modifie la maintenance
		$this->enregistre(array(
			
			'statut' => 4,
			'facture_vente_id' => $facture->modele->id,
		));
	
		return true;
	}

}
