<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Liste_libre;

class Export_management extends Element_management {

    /*
     *
     * On retourne le lien vers le fichier
     *
     */
    public function retourne_lien_fichier($modele){

        if(isset($modele->fichier))
            return '<a href="' .asset('storage/exports/' . $modele->fichier). '">' . $modele->fichier . '</a>';
        else
            return traduction('messages.php.exports.fichier_non_cree');
    }
    
    public function traduction_id_liste($modele){
        
        $traduction_id_liste = traduction('messages.php.exports.traduction_id_liste_non_cree');
        $liste_libres = Liste_libre::where('id', $modele->id_liste)->first();
        
        if(!empty($liste_libres->id_rapport))
            $traduction_id_liste = traduction('rapport.' . $liste_libres->id_rapport . '.titre');
        else
            $traduction_id_liste = traduction('tables_libres.' . $liste_libres->type_element . '.nom_table');
        
        return $traduction_id_liste;
    }
}