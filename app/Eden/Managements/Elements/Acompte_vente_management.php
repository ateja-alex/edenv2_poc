<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Acompte_vente_ligne;
use App\Eden\Variables;

class Acompte_vente_management extends Acompte_management {

	/**
	*
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	*
	*/
	public function modele_lignes() {

		return new Acompte_vente_ligne;
	}

	/**
	 *
	 * Retourne le bon code à utiliser pour les tiers
	 *
	 */
	public function compta_compte_tiers($article) {

		// on regarde si le client a un compte comptable spécifique
		if(!empty($this->modele->client_id_tiers_payeur))
			$client = modele('client', $this->modele->client_id_tiers_payeur);
		else
			$client = modele('client', $this->modele->client_id);

		if(!empty($client->forcer_compte_comptable))
			return $client->forcer_compte_comptable;

		return fonctionnalite('compta_compte_general_clients');
	}

	/**
	 *
	 * Certains clients souhaitent utiliser la date de facturation pour le module de recouvrement
	 * Il faut donc surcharger cette méthode
	 *
	 */
	public function date_a_utiliser_pour_recouvrement() {

		return 'date_de_reglement';
	}


    /**
     *
     * On gère la suppression par avoir
     *
     */
    public function supprime($modele = false) {

        $modification_possible = $this->verifie_cloture_comptable();

        if($modification_possible === false)
            return traduction('messages.php.document.modification_passe',null,[fonctionnalite('cloture_comptable_mensuelle_le')]);


        // si la fonctionnalité n'est pas activée, on laisse la suppression standard
        if(fonctionnalite('annulation_acompte_par_avoir') !== true) {

            return parent::supprime($modele);
        }

        if($modele === false && !empty($this->modele))
            $modele = $this->modele;

        // l'acompte est déjà annulée par un avoir
        if($this->modele->annulee_par_avoir == 1)
            return traduction('messages.php.acompte.annulation_impossible_avoir');

        // l'acompte n'est pas validé, on laisse le standard
        if($this->modele->valide != 1)
            return parent::supprime($modele);

        if(isset($this->test_suppression) && $this->test_suppression === true)
            return 'test_ok' ;

        // Creation de l'avoir
        $modifications = array(

            'commentaires' => 'Acompte à l\'origine de cet avoir: '. $modele->reference_document,
            'acompte_id_source' => $modele->id,
        );

        $retour = $this->transformer_document('avoir_vente', $modifications);

        // Modification commentaire de l'avoir
        if($retour[0] !== true)
            return $retour[0];

        $avoir = $retour[1];

        // Validation de l'avoir
        $avoir->valide();

        // Changement de statut de l'acompte
        $this->methodes_post_annulation_par_avoir($avoir);

        $this->enregistre_comme_regle();

        $avoir->enregistre_comme_regle();

        // Recrédit des crédits utilisés
        $this->recredite_credits_utilises();

        // on enregistre qu'il a été supprimé
        $this->enregistre(array('annulee_par_avoir' => 1));

        // on logue la suppression par avoir
        $this->enregistrer_log(Variables::$types_logs['annulation_par_avoir']);

        return true;
    }
	
	/**
	 *
	 * Mise à jour du total du document
	 *
	 */
	public function maj_total_document($enregistrement_classique = false) {
		
		$modifications = parent::maj_total_document($enregistrement_classique);
		
		if(!$this->existe())
			return $modifications;
		
		$decimales = fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif');
		
		// on va voir quels sont les avoirs liés à cet acompte
		$avoirs = modele('avoir_vente')->where('acompte_id_source', $this->modele->id)->get();
		
		$solde_acompte = round($this->modele->solde_document_ttc, $decimales);
		
		foreach($avoirs as $avoir) {
			
			$avoir_management = management('avoir_vente', $avoir->id, $avoir);
			
			$totaux_avoir = $avoir_management->calcule_total_document();
			
			if($solde_acompte >= round($totaux_avoir['solde_ttc'], $decimales)) {
				
				// on met à jour le solde de l'acompte
				$modification_acompte = array('solde_document_ttc' => round($solde_acompte - $totaux_avoir['solde_ttc'], $decimales));
				
				$this->enregistre_modele($modification_acompte);
				
				// on met à jour le solde de l'avoir
				$modification_avoir = array('solde_document_ttc' => 0, 'regle' => 1);
				
				$avoir_management->enregistre_modele($modification_avoir);
				
				$solde_acompte -= $totaux_avoir['solde_ttc'];
			}
			elseif($solde_acompte > 0 && $solde_acompte < round($totaux_avoir['solde_ttc'])) {
				
				
				// on met à jour le solde de l'avoir
				$modification_avoir = array('solde_document_ttc' => round($totaux_avoir['solde_ttc'] - $solde_acompte, $decimales));
				
				$avoir_management->enregistre_modele($modification_avoir);
				
				// on met à jour le solde de l'acompte
				$modification_acompte = array('solde_document_ttc' => 0);
				
				$this->enregistre_modele($modification_acompte);
				
				$solde_acompte = 0;
			}
			else {
				
				if(round($totaux_avoir['solde_ttc'], $decimales) != $avoir->solde_document_ttc) {
					
					$modification_avoir = array('solde_document_ttc' => round($totaux_avoir['solde_ttc'], $decimales));
				
					$avoir_management->enregistre_modele($modification_avoir);
				}
			}
		}
		
		return $modifications;
	}
	
	/**
	 *
	 * On regarde si une facture est liée à l'acompte, si c'est le cas on retire le lien
	 *
	 */
	protected function methodes_post_annulation_par_avoir($avoir) {
		
		parent::methodes_post_annulation_par_avoir($avoir);
		
		$this->gere_liaison_acompte_apres_annulation_par_avoir();
	}
	
	/**
	 * 
	 * On gère la suppression du lien entre l'acompte et la facture quand on crée un avoir pour la facture
	 * 
	 */
	public function gere_liaison_acompte_apres_annulation_par_avoir() {
		
		// pas d'acompte
		if(modele('lien_acompte_facture')->where('acompte_vente_id', $this->modele->id)->first() === null)
			return true;
		
		/*
		$liens = modele('lien_acompte_facture')->where('acompte_vente_id', $this->modele->id)->get();
		
		foreach($liens as $lien) {
			
			$lien->delete();
		}
		*/
		
		return true;
	}

    /**
     *
     * Affiche une liste de tags pour les listes
     *
     */
    public function tags_pour_liste($modele) {

		$tags = array();

		if($modele->valide != 1) {

			$tags[] = '<span class="badge badge-default">'.traduction('interface.listes.tags_pour_liste.pro_forma').'</span>';
		}
		else {

			if($modele->regle == 1) {

				$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.regle').'</span>';
			}
			else {

				$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.non_regle').'</span>';
			}
		}

		if($modele->annulee_par_avoir == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.annule_par_avoir').'</span>';
		}

		if($modele->comptabilise == 1) {

			$tags[] = '<span class="badge badge-success" style="background: #249e8e">'.traduction('interface.listes.tags_pour_liste.comptabilise').'</span>';
		}

		// si c'est une facture ecommerce ?
		if($modele->canal == 1) {

			$tags[] = '<span class="badge badge-warning" style="background: #5677ed">'.traduction('interface.listes.tags_pour_liste.ecommerce').'</span>';
		}

        if($modele->avoir_partiel == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.avoir_partiel').'</span>';
		}

		if($modele->avoir_total == 1) {

			$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.avoir_total').'</span>';
		}

		return implode('<br/>', $tags);
	}

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        // Si l'acompte est annulé par avoir, on empêche la création de nouveau avoir
        if($this->modele->annulee_par_avoir !== 1) {

            $transformations_possibles['avoir_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'avoir_vente']);

        }

        return parent::transformations_possibles($transformations_possibles);

    }

    public function affichage_modeles_de_relances(){

        $modeles_de_relances = [];

        for($i = 1; $i < 5; $i++){

            if(!empty(fonctionnalite('modele_relance_' . $i)))
                $modeles_de_relances[$i] = traduction('document.actions.transformations_possibles.modele_de_relance_' . $i);

        }

        return $modeles_de_relances;

    }

    /**
     *
     * Défini quelles sont les colonnes à afficher pour la saisie des documents
     *
     * Peut être surchargé pour ajouter des colonnes sur mesure
     *
     */
    public function colonnes_articles() {

        $colonnes_articles = parent::colonnes_articles();

        if(isset($colonnes_articles['eco_contribution']))
            unset($colonnes_articles['eco_contribution']);

        return $colonnes_articles;

    }

}
