<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champs_liste_formatee;
use URL;
use App\Eden\Variables;

class Fournisseur_management extends Element_management {


	/**
	 *
	 * Récupère les infos du fournisseur pour la gestion commerciale
	 *
	 * Cette méthode est appelée lorsqu'on crée ou modifie un document
	 *
	 */
	public function infos_fournisseur_pour_gestion_commerciale() {

		if(empty($this->modele) || empty($this->modele->id)) {

			return array(

				'modele' => modele('fournisseur'),
				'encours' => null,
				'adresses_facturation' => [],
				'adresses_livraison' => [],
			);
		}

		// l'encours
		$encours = modele('facture_achat')
			->where('fournisseur_id', $this->modele->id)
			->where('valide', 1)
			->zero_ou_null('regle')
			->sum('solde_document_ttc');

		$adresses_facturation = modele('adresse_interne')->get();
		$adresses_livraison = modele('adresse_interne')->get();

		$this->modele->affiche_lien = $this->affiche_lien();

		return array(

			'encours' => $encours,
			'modele' => $this->modele,
			'adresses_facturation' => $adresses_facturation,
			'adresses_livraison' => $adresses_livraison,
		);
	}


	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);


		if ( empty($modele['clef_interface_fournisseur']) ) {
			$modifications = array('clef_interface_fournisseur' => uniqid('', true) );
			$modele->clef_interface_fournisseur = $modifications['clef_interface_fournisseur'];
			$this->enregistre_modele($modifications);
		}

		if ( empty($modele['lien_interface_fournisseur']) ) {
			$modifications = array('lien_interface_fournisseur' => URL::to('/interface_fournisseur/'.$modele->id.'/'.$this->genere_clef_interface_fournisseur($modele).'/accueil'));
			$this->enregistre_modele($modifications);
		}
	}


	public function genere_clef_interface_fournisseur($modele) {
		return sha1('Conn3x10nF0urn1$$eur'.$modele->clef_interface_fournisseur);
	}


	/**
	 *
	 * Retourne le compte auxiliaire du client (utilisé en comptabilité)
	 *
	 */
	public function compte_auxiliaire() {

		if(!empty($this->modele->compte_auxiliaire))
			return $this->modele->compte_auxiliaire;

		return $this->cree_compte_auxiliaire_alphanum();
	}

	/**
	 *
	 * Calcul le compte auxiliaire du client sur X caractères
	 *
	 */
	public function cree_compte_auxiliaire_alphanum() {

		$nombre_caracteres = fonctionnalite('compta_compte_auxiliaire_nombre_caracteres');

		if(empty($nombre_caracteres)) {

			return false;
		}

		$chaine_alphanum = substr(retraite_caracteres_speciaux($this->cree_chaine_pour_compte_auxiliaire()), 0, $nombre_caracteres);

		$suffixe = '';

		while(modele('fournisseur')->sans_profils()->avec_inactifs()->where('compte_auxiliaire', $chaine_alphanum.$suffixe)->first() !== null) {

			$chaine_alphanum = substr($chaine_alphanum, 0, $nombre_caracteres - 2);

			if(empty($suffixe)) {

				$suffixe = '01';
			}
			else {

				$suffixe++;

				// pour avoir un zero initial
				$suffixe = substr('0'.$suffixe, -2);
			}
		}

		$compte_auxiliaire = $chaine_alphanum.$suffixe;

		$management = management('fournisseur', $this->modele->id);

		// on met à jour sur le client
		$management->enregistre(array('compte_auxiliaire' => $compte_auxiliaire));

		return $compte_auxiliaire;
	}

	/**
	 *
	 * Retourne la chaine de caractères à utiliser pour créer le compte auxiliaire en compta
	 *
	 * Cette méthode est prévue pour être surchargée
	 *
	 */
	protected function cree_chaine_pour_compte_auxiliaire() {

		return $this->modele->nom;
	}

    /**
     *
     * On vérifie si le fournisseur a des documents avant de le supprimer
     *
     */
    public function supprime($modele = false) {

        if(!fonctionnalite('gescom_bloquer_suppression_fournisseur_si_presence_documents_commerciaux'))
            return parent::supprime($modele);

        // on doit vérifier s'il a un document de gestion commerciale
        foreach(Variables::$documents_achat_gescom as $type_element) {

            $first = modele($type_element)->sans_profils()->where('fournisseur_id', $this->modele->id)->first();

            if($first !== null)
                return traduction('messages.php.fournisseur.suppression_avec_document_rattache');
        }

        return parent::supprime($modele);
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'echange';
        $liste_options[] = 'mail';

        return $liste_options;
    }

    /**
     *
     * Retourne la première adresse de facturation (sous forme d'objet)
     *
     */
    public function premiere_adresse_facturation_objet() {

        $adresse_de_facturation = modele('adresse')
            ->where('fournisseur_id', $this->modele->id)
            ->where(function ($query) {
                $query->where('type_adresse', 2)
                    ->orWhere('type_adresse', 3)
                    ->orWhere('type_adresse', 0)
                    ->orWhere('type_adresse', 4)
                    ->orWhereNull('type_adresse');
            })
            ->first();

        if($adresse_de_facturation == null)
            return false;

        return $adresse_de_facturation;
    }

    public function retourne_sous_formulaire(){

        $retour = parent::retourne_sous_formulaire();

        $retour[] = [
            'type_element_enfant' => 'adresse',
            'type_element_remplacement' => 'adresse_de_facturation_standard',
            'champ_liaison' => 'fournisseur_id',
            'data_vue' => json_encode(['type_adresse' => 1]),
            'remplacement_supplementaire' => [
                [
                    "#formulaire.adresse.adresse#",
                    "#formulaire.adresse.adresse_de_facturation#"
                ]
            ],
            'optionnel' => 1,
            'unique' => 1,
        ];

        return $retour;

    }
}
