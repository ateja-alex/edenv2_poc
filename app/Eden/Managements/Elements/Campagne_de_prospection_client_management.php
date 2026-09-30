<?php

namespace App\Eden\Managements\Elements;

class Campagne_de_prospection_client_management extends Element_management {

    public function enregistre($modifications = array(), $modele = false){

        $campagne_de_prospection_id = null;

        if(isset($modifications['campagne_de_prospection_id']))
            $campagne_de_prospection_id = $modifications['campagne_de_prospection_id'];
        elseif ($this->existe())
            $campagne_de_prospection_id = $this->modele->campagne_de_prospection_id;

        $client_id = null;

        if(isset($modifications['client_id']))
            $client_id = $modifications['client_id'];
        elseif ($this->existe())
            $client_id = $this->modele->client_id;

        if((!empty($modifications['campagne_de_prospection_id']) && !empty($client_id))
            || (!empty($modifications['client_id']) && !empty($campagne_de_prospection_id))){

            $lien_existant = modele('campagne_de_prospection_client')
                ->where('campagne_de_prospection_id',$campagne_de_prospection_id)
                ->where('client_id',$client_id);

            if($this->existe())
                $lien_existant->where('id','!=',$this->modele->id);

            $lien_existant = $lien_existant->first();

            if($lien_existant !== null){

                //Dans le cas, d'un import on renvoie la ligne à true pour la passer sans déclencher une erreur d'import
                if(defined('import_en_cours'))
                    return management('campagne_de_prospection_client',$lien_existant->id,$lien_existant)->enregistre($modifications);

                return traduction('messages.php.campagne_de_prospection_client.client_deja_ajoute');
            }
        }

        return parent::enregistre($modifications, $modele);
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();
        $liste_options[] = 'client_campagne';

        return $liste_options;
    }

    /**
     *
     * Action qui se déroule à la suite d'une réponse à un questionnaire lié à l'élément
     *
     */
    public function action_post_reponse_questionnaire($repondant_id){

        if($this->modele->statut != 2) {
            $this->enregistre_modele(array(
                'utilisateur_id' => moi()->id,
                'statut' => 2,
            ));
        }
    }

    /**
     *
     * Permet de récupérer les informations d'un questionnaire
     *
     */
    public function recupere_questionnaire($questionnaire_id){

        $management_questionnaire = management('questionnaire',$questionnaire_id);

        $donnees = $management_questionnaire->questions();

        $donnees['questionnaire'] = $management_questionnaire->modele;

        $parametre = [
            'type_element' => 'client',
            'element_id' => $this->modele->client_id,
            'type_element_origine' => 'campagne_de_prospection',
            'element_origine_id' => $this->modele->campagne_de_prospection_id
        ];

        if(empty($management_questionnaire->modele->reponses_multiples))
            $donnees['reponse_questionnaire'] = $management_questionnaire->reponses($parametre);

        return $donnees;
    }

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

		if(!empty($this->modele->client_id) && !empty($this->modele->campagne_de_prospection_id))
			return route('campagne_de_prospection.fiche_element', ['client', $this->modele->client_id, $this->modele->campagne_de_prospection_id]);

		return route('base_eden.liste.index', [$this->_type_element]);
	}
}
