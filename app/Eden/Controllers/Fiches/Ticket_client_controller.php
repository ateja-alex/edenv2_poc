<?php	

namespace App\Eden\Controllers\Fiches;	

use App\Eden\Controllers\Fiche_controller;
use App\Eden\Models\Element_piece_jointe;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Ticket_client_controller extends Fiche_controller {	

	public function taches() {

        $taches = modele('tache')->where('suivi_recette_easydev_id', $this->id_element)->get();

        $modele_par_defaut = modele_par_defaut('tache');

		return response()->json(array('taches' => $taches,'modele_par_defaut' => $modele_par_defaut));
    }

    /*
     *
     * Récupère les échanges du ticket et ajoute certaines infos sur ceux-ci comme les pièces jointes et l'auteur.
     *
     */
    public function echanges_ticket($ticket_client_id){

        $echanges = modele('ticket_client_echange')
            ->where('suivi_recette', $ticket_client_id)
            ->leftJoin('element_log',function($join){
                $join->on('id_element','ticket_client_echange.id')
                    ->where('element_log.type_element','ticket_client_echange')
                    ->where('type_action',1);
            })
            ->select('ticket_client_echange.*',
                DB::raw('IF(id_utilisateur > 0,"utilisateur",type_element_modificateur) as type_element_createur'),
                DB::raw('IF(id_utilisateur > 0,id_utilisateur,element_id_modificateur) as element_id_createur')
            )
            ->orderBy('ticket_client_echange.cree_le', 'desc');

        if(empty(moi()))
            $echanges = $echanges->where(function($where){
                $where->whereNull('type_message')->orWhere('type_message', '!=', 1);
            });

        $echanges = $echanges->get();

        foreach ($echanges as $echange) {

            //On récupère l'auteur
            if(!empty($echange['cree_par'])) {

                $utilisateur = modele('utilisateur')->where('id',$echange['cree_par'])->select(DB::raw(" CONCAT(utilisateur.prenom,' ',utilisateur.nom) as auteur , utilisateur.* "))->first();
                $echange->auteur = $utilisateur->auteur;
            }
            else
                $echange->auteur = $echange->auteur_extranet;

            $echange->cree_le = 'le '.formate_date('d/m/Y à H:i', $echange['cree_le']);

            if(empty($echange->mail_html))
                $echange->message = nl2br($echange['message']);

            //On ajoute les pièces jointes
            if(!empty($echange->pieces_jointes))
                $echange->pieces_jointes = Element_piece_jointe::whereIn('id', json_decode($echange->pieces_jointes))->get();
        }

        return response()->json($echanges);
    }
}