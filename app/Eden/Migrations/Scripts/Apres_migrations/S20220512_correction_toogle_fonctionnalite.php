<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;

class S20220512_correction_toogle_fonctionnalite implements Script {

    public function execute() {


        $management_fonctionnalites_generales = management_fonctionnalite('fonctionnalite');

        $toutes_les_fonctionnalites = $management_fonctionnalites_generales->toutes_les_fonctionnalites();
        $fonctionnalite_toggle = array();

        foreach ($toutes_les_fonctionnalites as $categorie => $fonctionnalites_categorie){

            foreach ($fonctionnalites_categorie as $fonctionnalite){

                if($fonctionnalite['type'] == "toggle"){
                    $fonctionnalite_toggle[] = $fonctionnalite['fonctionnalite'];
                }
            }
        }

        $fonctionnalites_storage = array();

        if(file_exists(storage_path('app/eden_fonctionnalites.php')))
            $fonctionnalites_storage = include(storage_path('app/eden_fonctionnalites.php'));

        foreach ($fonctionnalite_toggle as $fonctionnalite_toggle){

            // On a un toggle a '' au lieu de false
            if (array_key_exists($fonctionnalite_toggle, $fonctionnalites_storage) && $fonctionnalites_storage[$fonctionnalite_toggle] !== true && $fonctionnalites_storage[$fonctionnalite_toggle] != '1')
                $fonctionnalites_storage[$fonctionnalite_toggle] = false;
            else if (array_key_exists($fonctionnalite_toggle, $fonctionnalites_storage) && $fonctionnalites_storage[$fonctionnalite_toggle] == '1')
              $fonctionnalites_storage[$fonctionnalite_toggle] = true;
        }

        $contenu_fichier = "<?php\n\nreturn [\n";

        $verfication_conditionnement = false;

        // On va activer automatique "unite" en colonne si conditionnement activé
        foreach($fonctionnalites_storage as $nom => $valeur) {

            if ($nom === "utiliser_conditionnement" && $valeur == "true")
                $verfication_conditionnement = true;

        }

        if($verfication_conditionnement){
            $fonctionnalites_storage['documents_colonnes_a_afficher_vente']["unite"] = "true";
            $fonctionnalites_storage['documents_colonnes_a_afficher_achat']["unite"] = "true";
        }

        foreach($fonctionnalites_storage as $nom => $valeur) {

            if($valeur === true || $valeur === 'true')
                $contenu_fichier .= "\t'".$nom."' => true,\n";
            elseif($valeur === false || $valeur === 'false')
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

        return true;
    }
}
