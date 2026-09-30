<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;

class S20220615_encodage_base64_mdp_fonctionnalites implements Script {

    public function execute() {

        $management_fonctionnalites_generales = management_fonctionnalite('fonctionnalite');

        $toutes_les_fonctionnalites = $management_fonctionnalites_generales->toutes_les_fonctionnalites();
        $fonctionnalite_password = array();

        foreach ($toutes_les_fonctionnalites as $categorie => $fonctionnalites_categorie){

            foreach ($fonctionnalites_categorie as $fonctionnalite){

                if($fonctionnalite['type'] == "password"){
                    $fonctionnalite_password[] = $fonctionnalite['fonctionnalite'];
                }
            }
        }

        $fonctionnalites_storage = array();

        if(file_exists(storage_path('app/eden_fonctionnalites.php')))
            $fonctionnalites_storage = include(storage_path('app/eden_fonctionnalites.php'));

        foreach ($fonctionnalite_password as $fonctionnalite_password){

            // On encode le password
            if(!empty($fonctionnalites_storage[$fonctionnalite_password]))
                $fonctionnalites_storage[$fonctionnalite_password] = base64_encode($fonctionnalites_storage[$fonctionnalite_password]);
        }

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
