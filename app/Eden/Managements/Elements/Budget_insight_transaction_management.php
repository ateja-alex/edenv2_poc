<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Famille_theme_de_filtres;

class Budget_insight_transaction_management extends Element_management {
	
	/**
	 * 
	 * On ajoute des tags pour les listes
	 *
	 */
	public function liste_tags($modele) {

		$tags = array();
		
		if($modele->statut_eden == 1) {
			
			$tags[] = '<span class="badge badge-warning">Partiellement rapprochée</span>';
		}
		
		if($modele->statut_eden == 2) {
			
			$tags[] = '<span class="badge badge-success">Rapprochée</span>';
		}
		
		if($modele->statut_eden == 3) {
			
			if(defined('export_en_cours') &&  export_en_cours == 'excel') {
			
				$tags[] = "Reportée ($modele->commentaire)";
			}
			else {

				$tags[] = '<span class="badge badge-danger" data-toggle="tooltip" data-placement="right" title="'.$modele->commentaire.'">Reportée</span>';
			}
		}

		return implode(' ', $tags);
	}
	
	/**
	 * 
	 * On ajoute les données des paiements liés via l'intitulé
	 *
	 */
	public function liste_intitule($modele) {
		
		$intitule = array($modele->wording);
		
		if(in_array($modele->statut_eden, array(1,2))) {
			
			
			$paiements_lies = modele('paiement')->where('transaction_id', $modele->id)->get();
			
			foreach($paiements_lies as $paiement) {
				
				if(!empty($paiement->type_element)) {
					
					$management = management($paiement->type_element, $paiement->id_document);
					
					$intitule[] = $management->affiche_lien().' ('.montant($paiement->montant).' ' . maquette('devise_application_symbole') . ')';
				}
			}
		}
		
		return implode('<br/>', $intitule);
	}
	
	/**
	 * 
	 * On met à jour le statut de la transaction
	 * 
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		if($this->modele->reporte_eden == 1 && $this->modele->statut_eden != 3) {
			
			$this->enregistre_modele(array('statut_eden' => 3));
		}
		
		$this->maj_statut_rapprochement();
	}
	
	/**
	 * 
	 * Mise à jour du statut de rapprochement de la transaction
	 * 
	 */
	public function maj_statut_rapprochement() {
		
		$total_transaction = $this->modele->value;
		
		// total des paiements
		$paiements = modele('paiement')->where('transaction_id', $this->modele->id)->sum('montant');
		
		// c'est rapproché
		if(empty(round(abs($paiements - $total_transaction), 2))) {
			
			$this->enregistre_modele(array('statut_eden' => 2, 'a_rapprocher' => 0));
			
			return true;
		}
		
		// c'est partiellement rapproché
		if(!empty($paiements)) {
			
			$this->enregistre_modele(array('statut_eden' => 1, 'a_rapprocher' => round($total_transaction - $paiements, 2)));
			
			return true;
		}
		
		// il n'y a rien eu de fait
		return true;
	}

    /**
     *
     * Débit de la transaction
     *
     */
    public function debit($modele){

        if($modele->a_rapprocher < 0)
            return '<div style="text-align: right">'.montant(str_replace(',', '.', $modele->formatted_value)).' ' . maquette('devise_application_symbole') . '</div>';
    }

    /**
     *
     * Crédit de la transaction
     *
     */
    public function credit($modele){

        if($modele->a_rapprocher > 0)
             return '<div style="text-align: right">'.montant(str_replace(',', '.', $modele->formatted_value)).' ' . maquette('devise_application_symbole') . ' </div>';
    }

    public function liste_colonnes_options(){
        return ['zoom'];
    }

    /*
     *
     * On retourne les documents et paiements liés aux transactions
     *
     */
    public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

        $management = management('budget_insight_transaction');
        $paiements = modele('paiement')->where('transaction_id', $id_element)->get();
        $documents_lies = array();

        foreach($paiements as $paiement){

            if(!empty($paiement->type_element) && !empty($paiement->id_document))
                $paiement->document_lie = modele($paiement->type_element, $paiement->id_document);
        }

        // On appelle la vue qui affiche les infos que l'on veux via un render()
        $vue = "eden::listes.includes.details_ligne_budget_insight";
        
        return view($vue, array('lignes' => $paiements))->render();
    }
	
}