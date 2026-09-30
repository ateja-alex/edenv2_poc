<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Acompte_achat_ligne;
use App\Eden\Variables;

class Acompte_achat_management extends Acompte_management {
	
	/**
	* 
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	* 
	*/
	public function modele_lignes() {
		
		return new Acompte_achat_ligne;
	}

	public function liste_colonnes_options() {

        $liste_options = parent::liste_colonnes_options();

        if(in_array('supprimer',$liste_options))
            unset($liste_options[array_search('supprimer',$liste_options)]);
		
		return $liste_options;
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

        // la facture n'est pas validée, on laisse le standard
        if($this->modele->valide != 1)
            return parent::supprime($modele);

        if(isset($this->test_suppression) && $this->test_suppression === true)
            return 'test_ok' ;

        // Creation de l'avoir
        $modifications = array(

            'commentaires' => 'Acompte à l\'origine de cet avoir: '. $modele->reference_document,
        );

        $retour = $this->transformer_document('avoir_achat', $modifications);

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

        // on enregistre qu'elle a été supprimée
        $this->enregistre(array('annulee_par_avoir' => 1));

        // on logue la suppression par avoir
        $this->enregistrer_log(Variables::$types_logs['annulation_par_avoir']);

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

            $tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.valide').'</span>';
        }

        if($modele->annulee_par_avoir == 1) {

            $tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.annule_par_avoir').'</span>';
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

        $transformations_possibles['avoir_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'avoir_achat']);

        return parent::transformations_possibles($transformations_possibles);

    }
	
}