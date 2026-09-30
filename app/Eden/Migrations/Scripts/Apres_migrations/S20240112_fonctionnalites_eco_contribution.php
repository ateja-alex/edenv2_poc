<?php


namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Variables;
use File;
use Illuminate\Support\Facades\DB;

class S20240112_fonctionnalites_eco_contribution implements Script{

    public function execute(){

        if(!file_exists(storage_path('app/eden_fonctionnalites.php')))
            return true;

        $fonctionnalites_storage = include(storage_path('app/eden_fonctionnalites.php'));

        $fonctionnalites_storage['documents_colonnes_a_afficher_vente']["eco_contribution"] = false;
        $fonctionnalites_storage['documents_colonnes_a_afficher_achat']["eco_contribution"] = false;

        $contenu_fichier = "<?php\n\nreturn [\n";

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