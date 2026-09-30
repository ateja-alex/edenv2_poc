<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Formulaire;

class S20260126_liste_et_formulaire_intranet implements Script {

    public function execute() {

        if(file_exists(storage_path('app/eden_intranet.php'))){

            $structure_intranet = json_decode(file_get_contents(storage_path('app/eden_intranet.php')), true);

            foreach($structure_intranet['lignes'] as &$ligne){
                foreach($ligne['modules'] as &$module){
                    if($module['type_module'] == 'liste' && empty($module['liste'])){
                        $id_liste = Liste_libre::where('eden_listeslibres.type_element',$module['type_element'])
                            ->where('intranet',1)
                            ->join('eden_rapports','eden_rapports.id_rapport','eden_listeslibres.id_rapport')
                            ->value('eden_listeslibres.id');

                        $module['liste'] = $id_liste;

                        $module_formulaire_liste = $this->formulaire_type_element($structure_intranet, $module['type_element']);
                        if(!empty($module_formulaire_liste)){
                            $module['formulaire_liste'] = $module_formulaire_liste;
                        } else {
                            $module['formulaire_liste'] = "";
                        }
                    }

                    if($module['type_module'] == 'formulaire' && empty($module['nom_formulaire'])){
                        $formulaire_intranet = Formulaire::where('nom_formulaire', 'like', 'intranet_%')
                            ->where('type_element', $module['type_element'])
                            ->value('nom_formulaire');

                        if(!empty($formulaire_intranet))
                            $module['nom_formulaire'] = $formulaire_intranet;
                    }
                }
            }

            file_put_contents(storage_path('app/eden_intranet.php'), json_encode($structure_intranet));
        }

        return true;

    }

    public function formulaire_type_element($structure_intranet, $type_element){
        foreach($structure_intranet['lignes'] as $ligne){
            foreach($ligne['modules'] as $module){
                if($module['type_module'] == 'formulaire' && $module['type_element'] == $type_element){
                    return $module['id'];
                }
            }
        }

        return false;
    }
}
