<?php

namespace App\Eden\Controllers;

use App\Eden\Exceptions\Eden_exception;
use App\Http\Controllers\Controller;
use App\Eden\Models\Champ_libre;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class Mfiles_controller extends Controller {

    /*
     *
     * Récupère les classes disponibles dans M-files
     *
     */
    public function recuperer_classes($recuperer_documents = false){

        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept: application/json\r\n" .
                    "X-Authentication: ".parametre('mfiles_jeton_authentification')."\r\n".
                    "Content-Type: application/json\r\n",
            )
        );

        $context = stream_context_create($opts);

        $url = fonctionnalite('mfiles_url') . '/REST/structure/classes';

        if($recuperer_documents)
            $url .= '?objtype=0';

        $classes = json_decode(file_get_contents($url ,false,$context));
        $classes_formatees = array();

        foreach($classes as $classe){

            $classes_formatees[$classe->ID] = $classe;
        }

        return $classes_formatees;
    }

    /*
     *
     * Récupère les attributs disponibles dans M-files
     *
     */
    public function recuperer_attributs(Request $requete){

        $id = intval($requete->id);

        if($id === 0)
            $attributs_liaison = true;
        else
            $attributs_liaison = false;

        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept: application/json\r\n" .
                    "X-Authentication: ".parametre('mfiles_jeton_authentification')."\r\n".
                    "Content-Type: application/json\r\n",
            )
        );

        $context = stream_context_create($opts);

        $retour = file_get_contents(fonctionnalite('mfiles_url') . '/REST/structure/properties',false,$context);

        $attributs_a_retourner = array();
        $attributs = json_decode($retour);

        foreach($attributs as $attribut){

            if($attributs_liaison && ($attribut->DataType == 9 || $attribut->DataType == 10) && ($attribut->ObjectType == $id || $attribut->AllObjectTypes === true))
                $attributs_a_retourner[$attribut->ID] = $attribut;
            elseif($attributs_liaison === false && ($attribut->ObjectType == $id || $attribut->AllObjectTypes === true))
                $attributs_a_retourner[$attribut->ID] = $attribut;
        }
        return $attributs_a_retourner;
    }

    /*
     *
     * Récupère et stocke le token dans les paramètres eden (prochainement dans les fonctionnalités) et vérifie la validité de celui-ci
     *
     */
    public function connexion_mfiles(){

        $donnees_mfiles = request('donnees_mfiles');

        $donnees_requete = json_encode([
            'Username' => (!empty($donnees_mfiles['nom_utilisateur_mfiles'])) ? $donnees_mfiles['nom_utilisateur_mfiles'] : '',
            'Password' => (!empty($donnees_mfiles['mot_de_passe_mfiles'])) ? $donnees_mfiles['mot_de_passe_mfiles'] : '',
            'VaultGuid' => (!empty($donnees_mfiles['vault_guid'])) ? $donnees_mfiles['vault_guid'] : '',
        ]);

        // Requête pour demander le jeton d'accès
        $requete_access_token = curl_init();

        curl_setopt_array($requete_access_token, [
            CURLOPT_URL => fonctionnalite('mfiles_url') . '/REST/server/authenticationtokens',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => $donnees_requete
        ]);

        try {
            $reponse_requete_access_token = curl_exec($requete_access_token);

            $jeton_authentification = json_decode($reponse_requete_access_token)->Value;

            $validite_jeton = $this->verifie_validite_jeton($jeton_authentification);
        }
        catch(\Exception $e){
            $validite_jeton = false;
        }

        if($validite_jeton === true) {

            parametre('mfiles_jeton_authentification', $jeton_authentification);
            return response()->json(['succes' => true]);
        }
        else
            return response()->json(['succes' => false, 'erreur' => traduction('messages.php.mfiles.probleme_validite_jeton')]);
    }

    /*
     *
     * Récupère la classe avec l'id n°1 dans M-files pour vérifier si le token est valable
     *
     */
    public function verifie_validite_jeton($jeton_authentification){

        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept: application/json\r\n" .
                    "X-Authentication: ".$jeton_authentification."\r\n".
                    "Content-Type: application/json\r\n",
            )
        );

        $context = stream_context_create($opts);

        try{

            file_get_contents(fonctionnalite('mfiles_url') . '/REST/structure/classes/1',false,$context);
        } catch(\Exception $e){

             return false;
        }

        return true;
    }

    public function envoi_document_mfiles($type_document, $id_document){

        //On récupère le modèle et le pdf du document
        $document = management($type_document, $id_document);
        $chemin_pdf = $document->recupere_chemin_pdf();
        $pdf = file_get_contents(storage_path('app/'.$chemin_pdf));

        try {

            service('mfiles')->creer_document($document->modele->reference_document . '.pdf', $pdf, $type_document, $id_document);
        } catch(Eden_exception $e){

            return response()->json(['succes' => false, 'mfiles' => true, 'message' => $e->getMessage()]);
        }

        return response()->json(['succes' => true]);
    }
}