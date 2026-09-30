<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Elements\Utilisateur_management;

use App\Eden\Models\Element;
use App\Eden\Models\Parametre;


class Entite_management extends Element_management {

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * + on vide le cache à l'enregistrement d'un nouvel élément
	 * + on ajoute les paramètres par défaut pour la gestion commerciale
	 * + éventuellement, si l'utilisateur qui crée l'entité a un profil, on lui ajoute automatiquement cette entité
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        if(empty($modele_avant->id)) {

            // On vérifie si les paramètres sont présents et si non on les ajoute
            $parametres_defaut = [
                'numerotation_acompte_achat' => 'ACF{annee-date}{mois-date}{break}-{numero}',
                'numerotation_acompte_vente' => 'ACC{annee-date}{mois-date}{break}-{numero}',
                'numerotation_avoir_achat' => 'AF{annee-date}{mois-date}{break}-{numero}',
                'numerotation_avoir_vente' => 'AC{annee-date}{mois-date}{break}-{numero}',
                'numerotation_bl_achat' => 'BLF{annee-date}{mois-date}{break}-{numero}',
                'numerotation_bl_vente' => 'BLC{annee-date}{mois-date}{break}-{numero}',
                'numerotation_commande_achat' => 'CF{annee-date}{mois-date}{break}-{numero}',
                'numerotation_commande_vente' => 'CC{annee-date}{mois-date}{break}-{numero}',
                'numerotation_devis_achat' => 'DF{annee-date}{mois-date}{break}-{numero}',
                'numerotation_devis_vente' => 'DC{annee-date}{mois-date}{break}-{numero}',
                'numerotation_facture_achat' => 'FF{annee-date}{mois-date}{break}-{numero}',
								'numerotation_facture_vente' => 'FC{annee-date}{mois-date}{break}-{numero}',
                'numerotation_bon_retour_vente' => 'BRC{annee-date}{mois-date}{break}-{numero}',
                'numerotation_bon_preparation_vente' => 'BPC{annee-date}{mois-date}{break}-{numero}',
                'numerotation_bon_retour_achat' => 'BRF{annee-date}{mois-date}{break}-{numero}',
            ];

            if(!empty($modifications['entite_parent']))
                $parametres_defaut = Parametre::where('id_entite', $modifications['entite_parent'])
                    ->get()->pluck('valeur','nom')->toArray();


            foreach ($parametres_defaut as $nom_parametre => $valeur_defaut) {
                 parametre_entite($modele->id, $nom_parametre, $valeur_defaut);
            }
        }

		parent::methodes_post_modification($modele, $modele_avant, $modifications);
	}

    /**
     *
     * Permet d'ajouter des conditions particulières pour certains types d'élément pour les recherche
     *
     */
    public function conditions_specifiques_recherche($element, $filtrage = false) {

        if(moi()->acces_toutes_entites == 1 || editeur())
            return parent::conditions_specifiques_recherche($element, $filtrage);

        if($filtrage === false)
            $filtrage = [];

        $filtrage[] = array(
            'champ' => 'id',
            'condition' => 'whereIn',
            'valeur' => implode(',',moi()->entites),
        );

        return parent::conditions_specifiques_recherche($element, $filtrage);
    }

}
