<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Devis_vente_ligne;

use App\Eden\Variables;
use Illuminate\Support\Facades\DB;

class Devis_vente_management extends Devis_management {

	/**
	 *
	 * Retourne une instance du modèle pour gérer les lignes des documents
	 * Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	 * (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	 *
	 */
	public function modele_lignes() {

		return new Devis_vente_ligne;
	}

	/**
	 *
	 * Accepte un document (pour les devis)
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 */
	public function accepte() {

		$retour = parent::accepte();

		// mise à jour des variantes si nécessaire
		$this->maj_statuts_variantes_post_changement_statut();

		return $retour;
	}

	/**
	 *
	 * Met à jour les variantes après l'acceptation d'un devis
	 *
	 */
	public function maj_statuts_variantes_post_changement_statut() {

		// on gère les variantes
		if(fonctionnalite('gescom_variantes_devis') === false)
			return;

		if(empty($this->modele->variante_devis_vente_id)) {

			$variantes = modele('devis_vente')->avec_inactifs()->where(function($r){ $r->where('variante_devis_vente_id', $this->modele->id); })->get();
		}
		else {

			$variantes = modele('devis_vente')->avec_inactifs()->where(function($r){ $r->where('variante_devis_vente_id', $this->modele->variante_devis_vente_id)->orWhere('id', $this->modele->variante_devis_vente_id); })->get();
		}

		foreach($variantes as $variante) {

			$devis_management = management('devis_vente', $variante->id);

			// On enregistre que le devis est la variante d'un autre devis
			$devis_management->enregistre_modele(['variante' => 1]);

			// c'est le document que nous venons d'accepter, on passe car rien à faire
			if($variante->id == $this->modele->id)
				continue;

			// la variante est déjà refusée, on passe car rien à faire
			if(in_array($variante->accepte, array(2,3)))
				continue;

			// la variante n'est pas validée, on passe car rien à faire
			if(empty($variante->valide))
				continue;


			// on refuse la variante
			$devis_management->annule_devis();

		}


	}

	/**
	 *
	 *	Prépare les données pour l'affichage des variantes de devis
	 *
	 */
	public function affiche_variante_devis(){

		$chaine_affichage = parent::affiche();
		$chaine_a_afficher = "";

		if(!empty($this->modele->date))
			$chaine_a_afficher .= $this->modele->date . " - ";

		$chaine_a_afficher .= $chaine_affichage ;

		if(!empty($this->modele->objet))
			$chaine_a_afficher .= " - ". $this->modele->objet ;

		if(isset($this->modele->montant_document_ht) )
			$chaine_a_afficher .= " - ". $this->modele->montant_document_ht . " " . maquette('devise_application_symbole');

		return $chaine_a_afficher;
	}

	/**
	 *
	 * Retourne le modèle par défaut pour ce management
	 *
	 */
	public function modele_par_defaut() {

		$modele = parent::modele_par_defaut();

		$modele->date_expiration = date('Y-m-d', strtotime('now +'.fonctionnalite('delai_expiration_devis').' days'));

        return $modele;
	}

	/**
	 *
	 * ????
	 *
	 */
	public function Fin_de_travaux() {
		$donnees = array();

		$donnees['test'] = "Ceci est un test";

		return $donnees;
	}

	/**
	 *
	 * On vérifie la marge minimum
	 *
	 */
	public function valide() {

        $marge_mini = fonctionnalite('marge_mini_sur_documents_commerciaux');

		// pas de marge mini
		if(empty($marge_mini))
			return parent::valide();

		// il n'a pas le droit, on doit vérifier la marge mini
		$totaux = $this->calcule_total_document();

		if($totaux['marge_totale'] < $marge_mini)
			return traduction('messages.php.devis_vente.non_valide_marge_trop_faible');

		return parent::valide();
	}

	/**
	 *
	 * Aide contextuelle spécifique aux devis vente
	 *
	 */
	protected function aide_contextuelle_devis_vente() {

		if(empty($this->modele->accepte)) {

			return array('texte' => traduction('messages.php.aide_contextuelle.devis_vente.accepte'),'id_aide' => 'saisie_devis_vente_document_valide');
		}

		if($this->modele->accepte == 2)

			return array('texte' => '', 'id_aide' => 'générique');

		// on est sur un devis accepté, on propose la création de la commande si nécessaire
		// on regarde si le devis a été transformé en commande
		$documents_lies = $this->documents_lies('commande_vente');

		if(empty($documents_lies)) {

			return array(
                'texte' =>
				traduction('messages.php.aide_contextuelle.devis_vente.creation_commande_vente').', <span class="fa fa-fw fa-plus-circle"></span><br/>
				','id_aide' => 'saisie_devis_vente_document_accepte');
		}

		return '';
	}

	/**
	 *
	 * Trigger post acceptation d'un document
	 *
	 * @return void
	 *
	 */
	public function methodes_post_acceptation_document($modele) {

		parent::methodes_post_acceptation_document($modele);

		// On va mettre a jour les lignes potentielles du document
		$lignes_document = modele('devis_vente_lignes')->where('document_id',$modele->id)->get();

		if ($lignes_document->isEmpty())
			return;

		foreach ($lignes_document as $ligne) {

			$ligne->accepte = $modele->accepte;

			$ligne->save();
		}

	}

	/**
	 *
	 * Trigger post acceptation d'un document
	 *
	 * @return void
	 *
	 */
	public function methodes_post_refus_document($modele) {

		parent::methodes_post_refus_document($modele);

		// On va mettre a jour les lignes potentielles du document
		$lignes_document = modele('devis_vente_lignes')->where('document_id',$modele->id)->get();

		if ($lignes_document->isEmpty())
			return;

		foreach ($lignes_document as $ligne) {

			$ligne->accepte = $modele->accepte;

			$ligne->save();
		}
	}

	/**
	 *
	 * Trigger post acceptation d'un document
	 *
	 * @return void
	 *
	 */
	public function methodes_post_annulation_devis($modele) {

		parent::methodes_post_annulation_devis($modele);

		// On va mettre a jour les lignes potentielles du document
		$lignes_document = modele('devis_vente_lignes')->where('document_id',$modele->id)->get();

		if ($lignes_document->isEmpty())
			return;

		foreach ($lignes_document as $ligne) {

			$ligne->accepte = $modele->accepte;

			$ligne->save();
		}
	}

    /**
     *
     * Acceptation du devis suite à la signature
     *
     */
    public function methode_post_signature(){
        $this->accepte();
    }

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        if($this->modele->accepte == 2)
            return array();

        $transformations_possibles['devis_achat'] = route('document.transformer_fournisseur', [$this->_type_element, $this->modele->id, 'devis_achat']);

        // si le devis est accepté, on retourne toutes les transformations possible
        if($this->modele->accepte == 1) {

            $transformations_possibles['commande_vente'] =  route('document.transformer', [$this->_type_element, $this->modele->id, 'commande_vente']);
            $transformations_possibles['bon_preparation_vente'] =  route('document.transformer', [$this->_type_element, $this->modele->id, 'bon_preparation_vente']);
            $transformations_possibles['bl_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bl_vente']);
            $transformations_possibles['acompte_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'acompte_vente']);
            $transformations_possibles['facture_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'facture_vente']);
            $transformations_possibles['devis_achat'] = route('document.transformer_fournisseur', [$this->_type_element, $this->modele->id, 'devis_achat']);

            $transformations_possibles['commande_achat'] = 'transformer_fournisseurs_par_article_commande_achat';

            if(fonctionnalite('regroupement_articles_documents'))
                $transformations_possibles['commande_achat'] = 'transformer_fournisseurs_par_article_commande_achat_prefiltre';
        }
        else
            $transformations_possibles['devis_achat'] = route('document.transformer_fournisseur', [$this->_type_element, $this->modele->id, 'devis_achat']);

        return parent::transformations_possibles($transformations_possibles);

    }

    /**
     *
     * Pour recalculer les échéances
     *
     */
    protected function methodes_post_modification_document($modele, $modele_avant, $modifications) {

        parent::methodes_post_modification_document($modele, $modele_avant, $modifications);

        $this->recalcul_echeances();
    }

    /**
     *
     * Surcharge pour gérer correctement les variantes des devis
     *
     */
    protected function recupere_reference_document_pour_validation(){

        if(empty($this->modele->variante_devis_vente_id))
            return parent::recupere_reference_document_pour_validation();

        // c'est une variante, on ajoute le numéro de version
        $devis_vente_origine = modele($this->_type_element)
            ->avec_inactifs()->sans_profils()
            ->where('id', $this->modele->variante_devis_vente_id)->first();

        // le nombre de devis validés avec la même variante source
        $nombre_de_devis = modele($this->_type_element)->avec_inactifs()->sans_profils()->where('variante_devis_vente_id', $this->modele->variante_devis_vente_id)->where('valide', 1)->count();

        $nombre_de_devis++;

        $numerotation_variante = $devis_vente_origine->reference_document.'-v'.$nombre_de_devis;

        return $numerotation_variante;
    }

}
