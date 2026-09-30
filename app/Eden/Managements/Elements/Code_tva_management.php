<?php

namespace App\Eden\Managements\Elements;

use DB;
use App\Eden\Models\Champ_libre;

class Code_tva_management extends Element_management {

    /**
     *
     * On surcharge la méthode enregistre pour gérer les lignes des documents
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(isset($modifications['taux_tva_defaut']) && $modifications['taux_tva_defaut']){

            $count = modele('code_tva')->where('taux_tva_defaut', 1);

            if($this->existe())
                $count = $count->where('id', '!=', $this->modele->id);

            $count = $count->count();

            if($count >= 1)
                return traduction('messages.php.code_tva.erreur_taux_tva_defaut');
        }

        $retour = parent::enregistre($modifications, $modele);

        return $retour;
    }

    /**
     *
     * Bloque la suppression si le code TVA est utilisé
     *
     */
    public function supprime($modele = false) {

        $id_code_tva = $modele === false ? $this->modele->id : $modele->id;

        $resultats = $this->recupere_code_tva_utilise($id_code_tva)->groupBy('nom_table');

        if($resultats->isEmpty())
            return parent::supprime($modele);
        else{
            $erreur = traduction('messages.php.code_tva.erreur_suppression')."\n";

            foreach($resultats as $nom_table => $valeurs){
                $erreur .= traduction('tables_libres.'.$nom_table.'.nom_table')." : \n";
                foreach($valeurs as $valeur){
                    $erreur .= "ID : " . $valeur->id . " - " . $valeur->chaine_affichage . "\n";
                }  
            }

            return nl2br($erreur);
        }
    }

    public function recupere_code_tva_utilise($id_code_tva) {

        $champs_tva = Champ_libre::where('type_element_ajax', 'code_tva')->get();

        $resultats = collect();

        foreach($champs_tva as $tva){

            $lignes_tva_utilisee = modele($tva->type_element)->where($tva->nom_sql, $id_code_tva)->get();

            foreach($lignes_tva_utilisee as $element){
                $management_tva = management($tva->type_element, $element->id, $element);
                $resultats->push((object)[
                    'id' => $element->id,
                    'nom_table' => $tva->type_element,
                    'chaine_affichage' => $management_tva->affiche(),
                ]);
            }
        }

        return $resultats;
    }
}