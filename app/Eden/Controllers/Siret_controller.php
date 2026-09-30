<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

class Siret_controller extends Controller {

    public function affichage_test(){

        return view('eden::test_champ_siret');
    }

    public function recuperer_entreprise()
    {

        $recherche = request()->recherche;

        if($recherche!= null) {

            $service = service('Siret_v2_api');

            $entreprises = $service->execute($recherche);

            if (!$entreprises || !$entreprises["success"]) {
                return traduction('messages.php.siret.siret_introuvable');
            }

            $entreprises_formatees = array();

            foreach($entreprises["retour"] as $entreprise){

                $entreprise = $this->recuperer_adresse_entreprise($entreprise);
                $entreprise = $this->calculTvaIntra($entreprise);
                $entreprise = $this->recupere_nom($entreprise);

                $entreprises_formatees[] = $entreprise;
            }

            return $entreprises_formatees;
        }

        return 'Numéro de Siret invalide';
    }

    public function recuperer_adresse_entreprise($entreprise){

            $entreprise->adresse= $entreprise->numeroVoieEtablissement.' '.
                $entreprise->typeVoieEtablissement.' '.
                $entreprise->libelleVoieEtablissement.', '.
                $entreprise->codePostalEtablissement.', '.
                $entreprise->libelleCommuneEtablissement;

            return $entreprise;
    }



    public function recupere_nom($entreprise){
        if(!empty($entreprise->denominationUniteLegale)){
            $entreprise->nom = $entreprise->denominationUniteLegale;
        } else {
            $entreprise->nom = $entreprise->prenomUsuelUniteLegale.' '.$entreprise->nomUniteLegale;
        }
        return $entreprise;
    }

    public function calculTvaIntra($entreprise){

        $tva = "FR". $this->calculTVA($entreprise->siren).$entreprise->siren;

        $entreprise->tva = $tva;
        return $entreprise;
    }

    public function calculTVA($siren) {

        // Formule :
        // Clef TVA = ( ( (Siren Modulo 97) * 3 ) + 12 ) Modulo 97

        $sClef=( ( ($siren % 97) *3 ) + 12 ) % 97;
        // Modif Janvier 2012
        if ($sClef<10) {
            $sClef="0".$sClef ;
        }

        return $sClef;
    }


}
