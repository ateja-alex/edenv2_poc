<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Liste_libre;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Gestion_des_stocks_controller extends Controller{

    /**
     *
     * On affiche les stocks
     *
     */
    public function afficher() {

        $liste_libre = Liste_libre::where('type_element','stocks')
            ->where(DB::raw("coalesce(id_rapport,'')"),'')
            ->first();

        $listes_id = array();

        if(empty($liste_libre))
            exception("Aucun liste libre sur la table stocks n'a été trouvé");

        $listes_id[] = $liste_libre->id;

        $id_liste_conditionnement = null;

        if(presence_conditionnement()){

            $liste_libre_conditionnement = Liste_libre::where('type_element','stocks_par_conditionnement')
                ->where(DB::raw("coalesce(id_rapport,'')"),'')->first();

            if(empty($liste_libre_conditionnement))
                exception("Aucun liste libre sur la table stocks_par_conditionnement n'a été trouvé");

            $id_liste_conditionnement = $liste_libre_conditionnement->id;

            $listes_id[] = $id_liste_conditionnement;
        }

        $entrepots = modele('entrepot')->get();

        $id_liste_tous_les_entrepots = null;
        $id_liste_tous_les_entrepots_conditionnement = null;

        if(count($entrepots) > 1){
            $liste_libre_tous_les_entrepots = Liste_libre::where('type_element','stocks_tous_les_entrepots')
                ->where(DB::raw("coalesce(id_rapport,'')"),'')
                ->first();

            $id_liste_tous_les_entrepots = $liste_libre_tous_les_entrepots->id;

            $listes_id[] = $id_liste_tous_les_entrepots;

            if(presence_conditionnement()){
                $liste_libre_tous_les_entrepots_conditionnement = Liste_libre::where('type_element','stocks_tous_les_entrepots_par_conditionnement')
                    ->where(DB::raw("coalesce(id_rapport,'')"),'')->first();

                if(empty($liste_libre_tous_les_entrepots_conditionnement))
                    exception("Aucun liste libre sur la table stocks_tous_les_entrepots_par_conditionnement n'a été trouvé");

                $id_liste_tous_les_entrepots_conditionnement = $liste_libre_tous_les_entrepots_conditionnement->id;

                $listes_id[] = $id_liste_tous_les_entrepots_conditionnement;
            }
        }

        if(empty($entrepots))
            exception("Aucun entrepot n'a été trouvé");

        $donnees = array(
            'id_liste'=> $liste_libre->id,
            'id_liste_conditionnement'=> $id_liste_conditionnement,
            'id_liste_tous_les_entrepots' => $id_liste_tous_les_entrepots,
            'id_liste_tous_les_entrepots_conditionnement' => $id_liste_tous_les_entrepots_conditionnement,
            'listes_id'=> $listes_id,
            'entrepots'=> count($entrepots) > 10 ? false : $entrepots,
            'entrepot_par_defaut' => Arr::first($entrepots)
        );

        // On intègre les filtres passés dans la requête
        $donnees['indicateur_source'] = '';
        if(!empty(request()->all()))
            $donnees['indicateur_source'] = json_encode(request()->all());

		return view('eden::gestion_des_stocks', $donnees);
    }

    /**
     *
     * On enregistre les mouvement de stock pour ajuster le stock depuis l'inventaire tournant
     *
     */
    public function ajustement_stock(Request $request){

        $lignes = $request->lignes;

        $table = 'stocks_par_conditionnement';

        $stocks_sans_conditionnement = !empty($request->stocks_sans_conditionnement) && $request->stocks_sans_conditionnement != 'false' ? true : false;

        if($stocks_sans_conditionnement)
            $table = 'stocks';

        $stocks = modele($table)
            ->whereIn('id', array_keys($lignes))
            ->get()->keyBy('id');

        if(!$stocks_sans_conditionnement)
            $conditionnements = modele('conditionnement')
                ->whereIn('id', $stocks->pluck('conditionnement_id')->toArray())->get()->keyBy('id');

        foreach ($lignes as $id_ligne => $quantite_reel) {

            $stock = $stocks[$id_ligne];

            $conditionnement = null;

            if (!empty($stock->conditionnement_id))
                $conditionnement = $conditionnements[$stock->conditionnement_id];

            if ((empty($quantite_reel) && $quantite_reel != 0) || empty($stock))
                return response()->json(true);

            $stock_actuel = $stocks_sans_conditionnement ? $stock->stock_actuel : $stock->stock_actuel_conditionnement;

            $ecart_stock = $quantite_reel - $stock_actuel;

            if (empty($ecart_stock))
                return response()->json(true);

            $management_mouvement_stock = management('mouvement_de_stock');

            $quantite_par_contenant = 1;

            if (!empty($conditionnement))
                $quantite_par_contenant = $conditionnement->quantite;

            // Si l'écart est positif on ajoute du stock sinon, on le supprime
            $modifications_mouvement_stock = [

                'date' => date('Y-m-d'),
                'entrepot_id' => $stock->entrepot_id,
                'article_id' => $stock->article_id,
                'quantite' => $ecart_stock * $quantite_par_contenant,
                'type_de_mouvement' => 2,
                'conditionnement_id' => $stock->conditionnement_id,
                'quantite_conditionnement' => $ecart_stock,
                'reserve' => 0,
            ];

            $retour = $management_mouvement_stock->enregistre($modifications_mouvement_stock);
        }

        return response()->json(true);

    }

}
