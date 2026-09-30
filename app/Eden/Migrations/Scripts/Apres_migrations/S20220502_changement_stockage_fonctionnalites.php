<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;

class S20220502_changement_stockage_fonctionnalites implements Script {

    public function execute() {

        $array_types_fonctionnalites = array('integrations','crm');

        $fonctionnalites_a_merge = array();

        foreach ($array_types_fonctionnalites as $types_fonctionnalite){

            if(file_exists(storage_path('app/eden_fonctionnalites_'.$types_fonctionnalite.'.php'))) {

                $specifique_temporaire = include(storage_path('app/eden_fonctionnalites_'.$types_fonctionnalite.'.php'));

                $management_module = management_fonctionnalite($types_fonctionnalite.'_v2');

                if($management_module == null)
                    $management_module = management_fonctionnalite($types_fonctionnalite);

                $fonctionnalites_module = $management_module->liste_fonctionnalites();

                $noms_fonctionnalite = array();

                foreach ($fonctionnalites_module as $la_fonctionnalite){

                    $noms_fonctionnalite[] = $la_fonctionnalite['fonctionnalite'];
                }

                $specifique = array();

                foreach ($noms_fonctionnalite as $nom_fonctionnalite){

                    if(isset($specifique_temporaire[$nom_fonctionnalite]))
                        $specifique[$nom_fonctionnalite] = $specifique_temporaire[$nom_fonctionnalite];
                }

                $fonctionnalites_a_merge[$types_fonctionnalite] = $specifique;
            }
        }

        $specifique_general = array();

        if(file_exists(storage_path('app/eden_fonctionnalites.php')))
            $specifique_general = include(storage_path('app/eden_fonctionnalites.php'));

        foreach ($array_types_fonctionnalites as $types_fonctionnalite){

            if(!empty($specifique_general) && !empty($fonctionnalites_a_merge[$types_fonctionnalite])) {

                foreach ($fonctionnalites_a_merge[$types_fonctionnalite] as $fonctionnalite_du_module => $valeur) {

                    $specifique_general[$fonctionnalite_du_module] = $valeur;
                }
            }
        }

        $contenu_fichier = "<?php\n\nreturn [\n";

        $verfication_conditionnement = false;

        // On va activer automatique "unite" en colonne si conditionnement activé
        foreach($specifique_general as $nom => $valeur) {

            if ($nom === "utiliser_conditionnement" && $valeur == "true")
                $verfication_conditionnement = true;

        }

        if($verfication_conditionnement){
            $specifique_general['documents_colonnes_a_afficher_vente']["unite"] = "true";
            $specifique_general['documents_colonnes_a_afficher_achat']["unite"] = "true";
        }

        foreach($specifique_general as $nom => $valeur) {

            if($valeur === true)
                $contenu_fichier .= "\t'".$nom."' => true,\n";
            elseif($valeur === false)
                $contenu_fichier .= "\t'".$nom."' => false,\n";
            elseif(is_array($valeur)){

                $contenu_fichier .= "\t'".$nom."' => [\n";

                foreach($valeur as $nom_element => $element){

                    if($element == 'true'){
                        $contenu_fichier .= "\t\t'".$nom_element."' => true,\n";
                    }

                    else{
                        $contenu_fichier .= "\t\t'".$nom_element."' => false,\n";
                    }
                }

                $contenu_fichier .= "\t ], \n";
            }
            else {

                $contenu_fichier .= "\t'".$nom."' => \"".$valeur."\",\n";
            }
        }

        $contenu_fichier .= "];";

        // on stocke dans un fichier
        \Storage::put('eden_fonctionnalites.php', $contenu_fichier);

        foreach ($array_types_fonctionnalites as $types_fonctionnalite){

            if(file_exists(storage_path('app/eden_fonctionnalites_'.$types_fonctionnalite.'.php'))) {

                \Storage::delete('eden_fonctionnalites_'.$types_fonctionnalite.'.php');
            }
        }

        return true;
    }
}
