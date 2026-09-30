<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Variables;

class Profil_management extends Element_management{

    /**
     *
     * L'historique du document pour la page de saisie
     *
     */
    public function historique(){

        // On va chercher l'ensemble de l'historique
        $historique = parent::historique();

        $profil_droits_elements = modele('profil_droits_element')->avec_inactifs()->where('profil_id',$this->modele->id)->get();
        $entites = modele('entite')->get()->keyBy('id');

        foreach($profil_droits_elements as $profil_droit_element){

            $management_droits = management('profil_droits_element',$profil_droit_element->id,$profil_droit_element);

            $historique_droits_element = $management_droits->historique();

            foreach($historique_droits_element as $historique_droit_element){

                $informations_supplementaires = [];

                if(!empty($profil_droit_element->nom_sql))
                    $informations_supplementaires[] = traduction('champs_libres.'.$profil_droit_element->type_element.'.'.$profil_droit_element->nom_sql.'.nom');

                if(!empty($profil_droit_element->entite_id))
                    $informations_supplementaires[] = $entites[$profil_droit_element->entite_id]->nom;

                $historique_droit_element->intitule = traduction(Variables::$historique_intitule[$historique_droit_element->type_action][1])
                    .' | '.
                    traduction('tables_libres.'.$profil_droit_element->type_element.'.nom_table').
                    (!empty($informations_supplementaires) ? ' ('.implode(',',$informations_supplementaires).')' : '');
            }

            $historique = $historique->merge($historique_droits_element);
        }

        return $historique->sortByDesc('date')->values();
    }

}