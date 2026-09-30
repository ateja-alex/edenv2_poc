<?php

namespace App\Eden\Controllers;

use App\Eden\Librairies\Budgea\Exception;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Eden\Variables;

use Log;
use Illuminate\Support\Facades\Http;

class Email_controller extends Controller
{

    public function initialisation(){

        $parametres = request()->all();

        $email_service = service('email');

        $type_element = $parametres['type_element'] ?? null;

        $ids_elements = $parametres['ids_elements'] ?? [$parametres['id_element']] ?? null;

        if(!empty($type_element)){
            $type_element = service('vue_sql')->recupere_type_element($type_element);

            $elements = modele($type_element)->whereIn('id',$ids_elements)->get();

            $modeles_emails = $email_service->modeles_emails($type_element,$elements);
        }

        $comptes_emails = $email_service->comptes_emails();

        return response()->json([
            'comptes_emails' => $comptes_emails,
            'modeles_emails' => $modeles_emails ?? [],
            'type_element' => $type_element,
            'elements' => $elements ?? [],
            'champs_libres' => champs_libres($type_element),
        ]);
    }

    public function chargement_valeurs($type){

        $groupes_ids = request()->groupes_ids;
        $parametres = request()->parametres;

        $email_service = service('email');

        $type_element = $type == 'destinataires' ? 'parametrage_destinataire_email' : 'parametrage_piece_jointe_email';

        return response()->json(
            $email_service->chargement_elements($type_element, $groupes_ids, $parametres),
        );
    }

    public function gestion_publipostage(){

        $groupes_ids = request()->groupes_ids;
        $valeurs_a_publiposter = request()->valeurs_a_publiposter;
        $type_element = request()->type_element;

        $publipostage_service = service('publipostage');

        $valeurs_publipostes = [];

        foreach($groupes_ids as $groupe_id => $elements_ids) {
            
            $valeurs_publipostes[$groupe_id] = $publipostage_service->publipostage($valeurs_a_publiposter, $type_element, $elements_ids);
        }

        return response()->json([
            'valeurs_publipostes' => $valeurs_publipostes
        ]);
    }

    /**
     *
     * Envoie l'email
     *
     */
    public function envoyer(Request $formulaire){

        $formulaire = $formulaire->all();

        $expediteur = $formulaire['expediteur'];
        $mails = $formulaire['mails'];
        $type_element = $formulaire['type_element'];
        $template_email = $formulaire['template_email'];
        $enregistrer_echange = $formulaire['enregistrer_echange'];

        $compte_email = modele('compte_email', $expediteur['compte_email']);

        $mails_sans_destinataires = array_filter($mails, function($mail){
            return empty($mail["destinataires"][1]);
        });

        if(!empty($mails_sans_destinataires))
            return response()->json(['success' => false, 'message' => traduction('messages.php.email.erreur_destinataire_obligatoire')]);

        $email_service = service('email');

        $erreurs = false;

        foreach($mails as $mail){

            try {

                $mail_to = $mail["destinataires"][1] ?? [];
                $mail_cc = $mail["destinataires"][2] ?? [];
                $mail_bcc = $mail["destinataires"][3] ?? [];

                $pieces_jointes = array_map(
                    function($piece_jointe){

                        if(isset($piece_jointe['valeur']) && strpos($piece_jointe['valeur'],'eden/element') === 0){
                            $type_element = explode('/',$piece_jointe['valeur'])[2];
                            $element_id = explode('/',$piece_jointe['valeur'])[3];
                            $management_document = management($type_element,$element_id);
                            $piece_jointe['valeur'] = storage_path('app/'.$management_document->recupere_chemin_pdf());
                            $nom = $management_document->modele->reference_document;
                        }
                        else
                            $nom = $piece_jointe['affichage_select'] ?? $piece_jointe['affichage'];

                        $extension = pathinfo($piece_jointe['valeur'], PATHINFO_EXTENSION);

                        if(!str_ends_with($nom,'.'.$extension))
                            $nom .= '.'.$extension;

                        if(str_contains($piece_jointe['valeur'],'http://') || str_contains($piece_jointe['valeur'],'https://')){

                            $nom = basename($piece_jointe['valeur']);

                            $fichier = Http::withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                            ])->get($piece_jointe['valeur']);

                            if ($fichier->successful())
                                $base64_fichier = $fichier->body(); 
                            else
                                throw new \Exception("Erreur de téléchargement : " . $fichier->status());
                            
                            return [
                                'contenu_fichier' => $base64_fichier,
                                'informations' => [
                                    'as' => $nom,
                                    'mime' => $extension
                                ]
                            ];
                        }  

                        return [
                            'chemin' => $piece_jointe['valeur'],
                            'informations' => [
                                'as' => $nom
                            ]
                        ];
                    },
                    $mail["pieces_jointes"] ?? []
                );

                $management_statut_envoyer = [];

                foreach($mail['elements'] as $element){

                    if(fonctionnalite('enregistrer_systematique_email_en_echange') === true || $enregistrer_echange){
                        $echange = $email_service->prepare_tableau_donnees_echange($type_element, $element);
                        $email_service->enregistre_echange($type_element, $echange, $mail, $expediteur['compte_email'], $expediteur['alias_email'] ?? null);
                    }

                    if (in_array($type_element, Variables::$documents_gescom))
                        $management_statut_envoyer[] = management($type_element,$element);
                }

                $parametres_email = [
                    'type_configuration' => 2,
                    'id_compte_email' => $expediteur['compte_email'],
                    'alias' => $expediteur['alias_email'] ?? null,
                    'destinataire' => $mail_to,
                    'cc' => $mail_cc,
                    'bcc' => $mail_bcc,
                    'sujet' => $mail['sujet'],
                    'pieces_jointes' => $pieces_jointes,
                ];

                $variables_email = [
                    'contenu_email' => $mail['contenu'],
                    'compte_email' => $compte_email,
                    'titre' => $mail['sujet']
                ];

                $retour = $email_service->envoyer('eden::mails.'.$template_email, $variables_email, $parametres_email);

                if($retour !== true)
                    throw new \Exception($retour);

                foreach($management_statut_envoyer as $management){
                    $management->enregistre_modele(['envoye_par_mail' => 1]);
                    $management->log_envoi_par_mail();
                }
            }
            catch(\Exception $e){

                Log::info('Erreur mail multiple');

                if(isset($mail_to))
                    Log::info('Destinataires : '.json_encode($mail_to));

                Log::info($e);

                $erreurs = traduction('messages.php.email.mails_non_partis');
                continue;
            }
        }

        if($erreurs !== false)
            return response()->json(['success' => false, 'message' => $erreurs]);

        return response()->json(['success' => true, 'message' => traduction('messages.php.email.envoi_succes')]);
    }

    /**
     *
     *
     * Retourne l'image de la signature
     *
     */
    public function afficher_image_signature($id_compte_email){

        $compte_email = modele('compte_email', $id_compte_email);

        // image par défaut
        $extension = "png";

        $lien = "eden/images/bg_transparent.png";

        if (!empty(optional($compte_email)->image_signature)) {

            $image_signature = $compte_email->image_signature;
            $extension = strtolower(\File::extension($image_signature));

            $lien = "storage/$image_signature";
        }

        // Si l'image est un fichier de type jpg
        if ($extension == "jpg" || $extension == "jpeg") {

            header("Content-Type: image/jpeg");
            imagejpeg(imagecreatefromjpeg($lien));
        }

        // Si l'image est un fichier de type png
        if ($extension == "png") {

            header('Content-Type: image/png');
            imagepng(imagecreatefrompng($lien));
        }

        return response()->json(true);
    }
}
