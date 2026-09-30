<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
 *
 * Gestion des rapports
 *  
 */
class Analyse_coupon_reduction_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 * 
	 * On affiche les CA sur les 12 derniers mois
	 * 
	 */	
    public function genere($ajax = false) {
        
        $entites = $this->entites($this->rapport);
        $dates = $this->dates_mensuelles($this->rapport);
        $nb_utilisations = $this->nombre_utilisation($this->rapport);
        $ca_genere_fourchette = $this->ca_genere_fourchette($this->rapport);
        $valeur_fourchette = $this->valeur_fourchette($this->rapport);

        $factures_ventes_avec_coupon_de_reduction = 
            modele('facture_vente')
                ->join('coupon_reduction', 'coupon_reduction.id', 'facture_vente.coupon_reduction')
                ->join('entite', 'entite.id', 'facture_vente.entite_id')
                ->whereIn('facture_vente.entite_id', $entites)
                ->whereBetween('facture_vente.date', [$dates['date_debut'], $dates['date_fin']])
                ->whereNotNull('coupon_reduction')
                ->groupBy('facture_vente.coupon_reduction', 'facture_vente.entite_id')
                ->select(\DB::raw('count(facture_vente.coupon_reduction) as nb_code, 
                    SUM(facture_vente.montant_document_ht) as ca_generer, 
                    SUM(facture_vente.montant_reduction_coupon_reduction) as code, entite.nom as entite_nom, coupon_reduction.code as coupon_reduction_code')
                )
                ->get();

        // on filtre les factures selon les filtres choisis par l'utilisateur
        $factures_ventes_avec_coupon_de_reduction = $factures_ventes_avec_coupon_de_reduction
                ->whereBetween('nb_code', [$nb_utilisations['min'], $nb_utilisations['max']])
                ->whereBetween('ca_generer', [$ca_genere_fourchette['min'], $ca_genere_fourchette['max']])
                ->whereBetween('code', [$valeur_fourchette['min'], $valeur_fourchette['max']]);

        $tableau_coupons_reductions = [];

        // on formate le tableau
        foreach($factures_ventes_avec_coupon_de_reduction as $facture) {

            $tableau_coupons_reductions[$facture->coupon_reduction_code][$facture->entite_nom]['nb_code'] = $facture->nb_code;
            $tableau_coupons_reductions[$facture->coupon_reduction_code][$facture->entite_nom]['ca_generer'] = $facture->ca_generer;
            $tableau_coupons_reductions[$facture->coupon_reduction_code][$facture->entite_nom]['code'] = $facture->code;
        }

        $titres = array('#','code_promo', 'entite', "nombre_utilisations", 'ca_genere', 'remise_globale', 'familles');

        foreach($titres as &$titre){
            if($titre != '#')
                $titre = traduction('rapport.analyse_coupon_reduction.colonnes.'.$titre);
        }

        $this->rapport->titres($titres);

        $familles_reduction = [];
        
        $toutes_les_entites = modele('entite')->get();
        $tous_les_coupons_reductions = modele('coupon_reduction')->get();

        foreach($tableau_coupons_reductions as $code => $coupon) {

            foreach($coupon as $entite => $donnee) {

                $entite_id = $toutes_les_entites->where('nom', $entite)->first()->id;

                $familles_reduction[$code] = [];
                $coupon_reduction = $tous_les_coupons_reductions->where('code', $code)->where('entite_id', $entite_id)->first();
                
                $id_coupon_reduction = 0;

                if($coupon_reduction != null)  {

                    $id_coupon_reduction = $coupon_reduction->id;
                    $tableau_liste_famille = json_decode($coupon_reduction->liste_familles_coupon);
                    
                    if(is_array($tableau_liste_famille)) {

                        // si c'est vide, par défaut, c'est toutes les familles
                        if(empty($tableau_liste_famille)) {

                            $familles_reduction[$code] = array(0,13,272,128,95,42);
                        }
                        else {

                            $familles = modele('famille')->whereIn('id', $tableau_liste_famille)->get();
                      
                            foreach($familles as $famille) {
    
                                $familles_reduction[$code][] = management('article')->familles_produits_commandes($famille->id);
                            }
    
                            $familles_reduction[$code] = array_unique($familles_reduction[$code]);
                        }
                    }
                }

                $familles_a_afficher = '<div style="display:flex;justify-content:space-around">';
                
                if(!empty($familles_reduction[$code])){

                    if(in_array(0, $familles_reduction[$code]))
                        $familles_a_afficher .=  '<span class="badge badge-success">Sans valeurs</span>';
                    else
                        $familles_a_afficher .=  '<span class="badge"> Sans valeurs</span>';

                    if(in_array(13, $familles_reduction[$code]))
                        $familles_a_afficher .=  '<span class="badge badge-success">Lunch</span>';
                    else
                        $familles_a_afficher .=  '<span class="badge">Lunch</span>';
    
                    if(in_array(272, $familles_reduction[$code]))
                        $familles_a_afficher .=  '<span class="badge badge-success">Petit déjeuner</span>';
                    else
                        $familles_a_afficher .=  '<span class="badge">Petit déjeuner</span>';

                        
                    if(in_array(128, $familles_reduction[$code]))
                        $familles_a_afficher .=  '<span class="badge badge-success">Plateaux</span>';
                    else
                        $familles_a_afficher .=  '<span class="badge">Plateaux</span>';

                    if(in_array(95, $familles_reduction[$code]))
                        $familles_a_afficher .=  '<span class="badge badge-success">Buffet</span>';
                    else
                        $familles_a_afficher .=  '<span class="badge">Buffet</span>';

                    if(in_array(42, $familles_reduction[$code]))
                        $familles_a_afficher .=  '<span class="badge badge-success">Cocktail</span>';
                    else
                        $familles_a_afficher .=  '<span class="badge">Cocktail</span>';
                }

                $familles_a_afficher.="</div>";
                
                $route =  route('base_eden.fiche.index', ['type_element' => 'coupon_reduction', 'id' => $id_coupon_reduction]);

                $lien = "<a href='$route'>  $id_coupon_reduction </a>";

                $this->rapport->ligne(array($lien, $code, $entite, $donnee['nb_code'], montant($donnee['ca_generer']), montant($donnee['code']), $familles_a_afficher ));

            }
        }    

        return $this->rapport->genere($ajax);
    }
    
    public function between($val, $min, $max) {

        return ($val >= $min && $val <= $max);
    }
}