<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20220928_index_traduction_champs_utilisateur implements Script {

    public function execute() {

        // On récupère les index concernant les champs de la table utilisateur ayant une traduction spécifique
        $index_a_verifier = modele('traduction_valeur')
            ->where('index', 'like', 'champs_libres.utilisateur.%')
            ->whereNotNull('traduction_specifique')
            ->get()
            ->pluck('traduction_specifique', 'id');

        // On instancie le management qui servira à récupérer les champs
        $management_utilisateur = management('utilisateur');

        foreach ($index_a_verifier as $id => $traduction_specifique){

            // On vérifie si la traduction spé ne correspond pas au nom sql d'un champ
            try {

                $champ_libre = $management_utilisateur->champ($traduction_specifique);
            } catch(\Exception $e){

                // La traduction ne correspond pas au nom sql d'un champ, on continue
                continue;
            }
            
            // Si un champ correspond, on vide la traduction spé
            if(!empty($champ_libre))
                management('traduction_valeur', $id)->enregistre_modele(['traduction_specifique' => null]);
        }

        return true;
    }
}

