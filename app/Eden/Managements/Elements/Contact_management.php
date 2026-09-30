<?php

namespace App\Eden\Managements\Elements;


use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champs_liste_formatee;

class Contact_management extends Element_management {

	/**
	 *
	 * Retourne le lien vers l'élément
	 * 
	 * @param $id_element l'id élément en question. Si non fourni, on prendra l'id du modèle lié au management
	 * 
	 * @return string l'url d'affichage de l'élément (généralement une fiche ou un formulaire de création / modification)
	 * 
	 */
	public function lien_vers_element($id_element = false) {

		$table = table_libre($this->_type_element);

		if(empty($id_element))
			$id_element = $this->modele->id;

		if($table->fiche == 1)
			return route('base_eden.fiche.index', [$this->_type_element, $id_element, 'afficher']);

		$type_element = 'contact';

		if(empty($this->modele))
			$modele = modele('contact', $id_element);
		else
			$modele = $this->modele;

		// Le contact appartient à un client, on redirige vers sa fiche
		if(!empty($modele->client_id))
			return route('base_eden.fiche.index', ['client', $modele->client_id, 'afficher']);

		// Le contact appartient à un fournisseur, on redirige vers sa fiche
		if(!empty($modele->fournisseur_id))
			return route('base_eden.fiche.index', ['fournisseur', $modele->fournisseur_id, 'afficher']);

		return route('base_eden.liste.index', [$this->_type_element]);
	}

	/**
	 * 
	 * Gère la colonne options pour les listes
	 * 
	 * @param $modele le modèle en question
	 * @param $options array le tableau des options calculées par colonne_options()
	 * 
	 * @return html
	 * 
	 */
	public function liste_colonnes_options() {

        if(empty(moi()))
            return [];

        $liste_options = parent::liste_colonnes_options();

        if(!in_array('lien',$liste_options))
            $liste_options[] = 'lien';

        $liste_options[] = 'echange';

		return $liste_options;
	}

	/**
     *
     * On met à jour les tags des clients
     *
     */
    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        if(empty($modele->statut)){

            $modele->statut = 0;
            $modele->save();
        }

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

    }

    /**
     *
     * Récupérer les contacts d'un élément
     *
     */
    public function recuperer_contact_element($type_element_parent,$id_element_parent,$ordres = array(),$filtres = array()){

        $contacts = modele('contact')->where($type_element_parent.'_id', $id_element_parent);

        if($ordres != null){

            $contacts->orderBy(implode(',',$ordres));

        }

        $management_contact = management('contact');

        foreach($filtres as $champ => $valeur){

            $contacts = $management_contact->champ($champ)->applique_filtre_sur_requete($valeur,$contacts);
        }

        $contacts = $contacts->get();

        // pour tous les champs type 10,11,12, on instancie les bonnes valeurs
        $champs_libres = Champ_libre::where('type_element', 'contact')->get();

        $champs_libres_management = [];

        foreach($champs_libres as $champ_libre){

            $champs_libres_management[$champ_libre->nom_sql] = management('contact')->champ($champ_libre->nom_sql);
        }

        foreach($contacts as $contact) {

            $valeur_affichage = [];

            management('contact',$contact->id,$contact)->charge_valeurs_champs_multiselection($contact);

            //un tableau avec les valeurs issues de la table pivot
            foreach ($champs_libres as $champ_libre) {

                $nom_sql = $champ_libre->nom_sql;

                $valeur_affichage[$nom_sql] = $champs_libres_management[$nom_sql]->affiche($contact->{$nom_sql});

            }

            $contact->valeur_affichage = $valeur_affichage;

        }

        return $contacts;
    }

	/**
	 * 
	 * Retourne les actions sur les listes
	 * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
	 * 
	 */
	public function actions_a_afficher($id_liste) {

		// on recupère les actions principales
		$actions = parent::actions_a_afficher($id_liste);
		
		$actions['clients_campagne_questionnaire'] = '<span class="dropdown-item" @click="modale_envoi_questionnaire = true"><i class="fa fa-fw fa-question"></i> <span v-html="$root.traduction(\'interface.listes.envoyer_questionnaire_de_satisfaction\')"></span></span>';

		if(!empty(fonctionnalite('utiliser_extranet')))
			$actions['contact_ajout_extranet'] = '<span class="dropdown-item" @click="modale_ajout_extranet = true"><i class="fa fa-fw fa-user-plus"></i> <span v-html="$root.traduction(\'interface.listes.creation_compte_extranet_contact\')"></span></span>';
		
		return $actions;
	}
}
