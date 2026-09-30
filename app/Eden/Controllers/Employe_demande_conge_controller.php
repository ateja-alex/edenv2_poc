<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Employe_demande_conge_controller extends Controller {

    /*
     *
     * Changement de statut depuis la liste
     *
     */
    public function changement_statut_conge(Request $request){

        $donnees = $request->all();
        $retours = [];

        foreach ($donnees['ids'] as $demande_conge_id) {

            $demande_conge = management('employe_demande_conge', $demande_conge_id);

            $retour_validation = $demande_conge->validation_demande_conge($donnees['statut']);

            if($retour_validation !== true)
                $retours[] = ucfirst(table_libre('employe_demande_conge')->element).' '.$demande_conge_id.' : '.$retour_validation;
        }

        return response()->json(['retour'  => empty($retours), 'message' => implode('<br>',$retours)]);
    }

    /**
     *
     *
     * Changement de statut depuis le mail de validation
     *
     */
    public function validation_conge($id_demande, $reponse) {

        $management = management('employe_demande_conge', $id_demande);
        $type_retour = 'message';
        $message = traduction('messages.php.intranet.changement_statut_valide');

        $retour = $management->validation_demande_conge($reponse);

        if($retour !== true){

            $type_retour = 'erreur';
            $message = $retour;
        }

        return redirect()->route('base_eden.liste.index', ['type_element' => 'employe_demande_conge'])->with($type_retour, $message);
    }
}
