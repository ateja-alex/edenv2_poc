<?php

namespace App\Eden\Managements;


/**
 * Gestion des utilisateurs de l'ERP
 */
class Utilisateurs_management {

    /**
     * 
     * On récupère la liste des utilisateurs et on retraite certaines informations
     * Comme les entités par défaut, etc.
     * 
     */
    public function liste_utilisateurs() {

		$utilisateurs = modele('utilisateur')->liste_utilisateurs_visibles();
		
		if($utilisateurs->count() > 0) {
			
			foreach($utilisateurs as $utilisateur) {
				
				$utilisateur->entite_defaut = json_decode(base64_decode($utilisateur->entite_defaut),true);
				$utilisateur->entite_acces = json_decode(base64_decode($utilisateur->entite_acces),true);

                if(!is_array($utilisateur->entite_defaut))
                    $utilisateur->entite_defaut = array();
                else
                    $utilisateur->entite_defaut = array_keys($utilisateur->entite_defaut);

                if(!is_array($utilisateur->entite_acces))
                    $utilisateur->entite_acces = array();
                else
                    $utilisateur->entite_acces = array_keys($utilisateur->entite_acces);

                if($utilisateur->type_utilisateur == null)
                    $utilisateur->type_utilisateur = 0;

                $management = management('utilisateur', $utilisateur->id, $utilisateur);
                $management->charge_valeurs_champs_multiselection();

			}
		}
        
        return $utilisateurs;
    }

    /**
     * Fonction servent à mettre un utilisateur existant
     *
     * @param array     $donnees    Données provenant du formulaire
     *
     * @return boolean  retourne true si tout s'est bien passée sinon false
    public function mettre_a_jour_utilisateur($donnees) {

        $utilisateur = Treso_utilisateurs::find($donnees['id_utilisateur']);

        $this->mettre_a_jour_donnees_utilisateur($donnees, $utilisateur);

        return $utilisateur->save();
    }
     */

    /**
     * Fonction de mise à jour des donneées du modèle
     *
     * @param array     $donnees    Données à utiliser pour la mise à jour
     *
     * @param objet     $modele     Modele de l'utilisateur à mettre à jour
    private function mettre_a_jour_donnees_utilisateur($donnees,$modele) {

        foreach($donnees as $cle => $donnee) {
            
            $modele->$cle = $donnee;
        } 
    }
     */

    /**
     * Fonction créant une clé pour l'api en s'assurant qu'elle est unique
     *
     * @return la clé d'api
    private function cle_api() {

        $cle_api = md5(random_bytes(32));

        while(Treso_utilisateurs::where('api_cle_publique', $cle_api)->orWhere('api_cle_privee')->get()->count() > 0) {

            $cle_api = md5(random_bytes(32));
        }

        return $cle_api;
    }
     */
}
