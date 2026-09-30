<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Variables;

class S20260304_reprise_modification_gestion_email implements Script {

    public function execute(){

        return;

        $this->verification_reprise_possible();

        $this->reprise_donnees_modele_email();
        $this->reprise_donnees_questionnaire();

        $this->reprise_donnees_notifications_manuelles();

        $this->reprise_table_libre_envoyer_mail();

        $fonctionnalite_note_de_frais = fonctionnalite('compta_libelle_note_de_frais');
        $fonctionnalite_paiement = fonctionnalite('compta_libelle_paiement');
        $fonctionnalites_a_modifier = [];

        if(!empty($fonctionnalite_note_de_frais)){
            $fonctionnalite_note_de_frais_modif = $this->transcription_contenu_publipostage($fonctionnalite_note_de_frais, 'note_de_frais');

            if($fonctionnalite_note_de_frais_modif != $fonctionnalite_note_de_frais)
                $fonctionnalites_a_modifier['compta_libelle_note_de_frais'] = $fonctionnalite_note_de_frais_modif;
        }
        if(!empty($fonctionnalite_paiement)){
            $fonctionnalite_paiement_modif = $this->transcription_contenu_publipostage($fonctionnalite_paiement, 'paiement');

            if($fonctionnalite_paiement_modif != $fonctionnalite_paiement)
                $fonctionnalites_a_modifier['compta_libelle_paiement'] = $fonctionnalite_paiement_modif;
        }

        if(!empty($fonctionnalites_a_modifier))
            Script_management::modifier_fonctionnalites($fonctionnalites_a_modifier);

        return true;
    }

    public function verification_reprise_possible(){

        $modele_email_sans_type_element = modele('modele_email')->whereNull('type_element_id')->get();

        foreach($modele_email_sans_type_element as $modele_email){

            $contenu = ($modele_email->sujet_modele ?? '').' '.($modele_email->modele ?? '');

            preg_match_all('/\{\{\s*([^{}]+?)\s*\}\}/', $contenu, $matches);

            $types_elements = [];

            foreach(($matches[1] ?? []) as $variable){
                $type_element = trim(strtok($variable, '['));

                if(empty($type_element) || strpos($type_element, '#') === 0 || !table_libre_existe($type_element))
                    continue;

                $types_elements[] = $type_element;
            }

            $types_elements = array_values(array_unique($types_elements));

            if(count($types_elements) == 0)
                throw new \Exception('Reprise impossible pour le modèle email #'.$modele_email->id.' : aucun type_element détecté.');

            if(count($types_elements) > 1)
                throw new \Exception('Reprise impossible pour le modèle email #'.$modele_email->id.' : plusieurs type_element détectés ('.implode(', ', $types_elements).').');

            management('modele_email', $modele_email->id, $modele_email)->enregistre([
                'type_element_id' => table_libre($types_elements[0])->id
            ]);
        }

        $questionnaire_sans_type_element = modele('questionnaire')->whereNull('type_element_id')->get();

        foreach($questionnaire_sans_type_element as $questionnaire){

            $contenu = ($questionnaire->sujet ?? '').' '.($questionnaire->corps_du_mail ?? '');

            preg_match_all('/\{\{\s*([^{}]+?)\s*\}\}/', $contenu, $matches);

            $types_elements = [];

            foreach(($matches[1] ?? []) as $variable){
                $type_element = trim(strtok($variable, '['));

                if(empty($type_element) || strpos($type_element, '#') === 0 || !table_libre_existe($type_element))
                    continue;

                $types_elements[] = $type_element;
            }

            $types_elements = array_values(array_unique($types_elements));

            if(count($types_elements) == 0)
                throw new \Exception('Reprise impossible pour le questionnaire #'.$questionnaire->id.' : aucun type_element détecté.');

            if(count($types_elements) > 1)
                throw new \Exception('Reprise impossible pour le questionnaire #'.$questionnaire->id.' : plusieurs type_element détectés ('.implode(', ', $types_elements).').');

            management('questionnaire', $questionnaire->id, $questionnaire)->enregistre([
                'type_element_id' => table_libre($types_elements[0])->id
            ]);
        }

    }

    /**
     * 
     * Reprise des champs formats email et activation des mails automatiquement, pareil activation automatique pour les factures
     * 
     */
    public function reprise_table_libre_envoyer_mail(){

        $tables_libres_a_activer = Table_libre::where(function($condition){
            $condition->whereNull('envoyer_email')
                ->orWhere('envoyer_email',0);
        })->join('eden_champslibres as ec','ec.type_element','eden_tableslibres.type_element')
        ->where('ec.type', 0)->where('ec.format_champ', 'email')
        ->groupBy('eden_tableslibres.id')
        ->select('eden_tableslibres.*')
        ->get();

        foreach($tables_libres_a_activer as $table_libre_a_activer){

            $table_libre_a_activer->envoyer_email = 1;
            $table_libre_a_activer->save();

            if(file_exists(app_path()."/Migrations/".$table_libre_a_activer->type_element.".php"))
                Table_libre_management::generer_fichier_migration($table_libre_a_activer->type_element,true);
        }
    }

    /**
     * 
     * Reprise des pieces_jointes et des mails en copie 
     * 
     */
    public function reprise_donnees_modele_email(){

        $modeles_emails = modele('modele_email')->get();

        $champs_pieces_jointes = ['pj_1', 'pj_2', 'pj_3'];
        $champs_cc = ['cc_1', 'cc_2', 'cc_3'];
        $champs_bcc = ['bcc_1', 'bcc_2', 'bcc_3'];

        $tables_libres = Table_libre::get()->pluck('type_element', 'id');

        foreach($modeles_emails as $modele_email){

            foreach($champs_cc as $champ_cc){
                if(!empty($modele_email->$champ_cc)){
                    management('parametrage_destinataire_email')->enregistre([
                        'modele_email_id' => $modele_email->id,
                        'adresse_mail' => $modele_email->$champ_cc,
                        'type' => 2,
                        'niveau' => 2
                    ]);
                }
            }

            foreach($champs_bcc as $champ_bcc){
                if(!empty($modele_email->$champ_bcc)){
                    management('parametrage_destinataire_email')->enregistre([
                        'modele_email_id' => $modele_email->id,
                        'adresse_mail' => $modele_email->$champ_bcc,
                        'type' => 3,
                        'niveau' => 2
                    ]);
                }
            }

            foreach($champs_pieces_jointes as $champ_pj){
                if(!empty($modele_email->$champ_pj)){
                    management('parametrage_piece_jointe_email')->enregistre([
                        'modele_email_id' => $modele_email->id,
                        'piece_jointe' => $modele_email->$champ_pj,
                        'niveau' => 2
                    ]);
                }
            }

            $champs_libres = Champ_libre::where('type_element', 'modele_email')
                ->whereIn('nom_sql', array_merge($champs_pieces_jointes,$champs_cc,$champs_bcc))->get();

            foreach($champs_libres as $champ_libre){
                $champ_libre->delete();
            }

            // Si le fichier existe, on regénére le fichier de migrations
            if (file_exists(app_path() . '/Migrations/modele_email.php'))
                Table_libre_management::generer_fichier_migration('modele_email',true);

            $type_element_modele_email = $tables_libres[$modele_email->type_element_id] ?? null;

            if(empty($type_element_modele_email))
                continue;

            $sujet_modele = $this->transcription_contenu_publipostage($modele_email->sujet_modele, $type_element_modele_email);
            $modele = $this->transcription_contenu_publipostage($modele_email->modele, $type_element_modele_email);

            management('modele_email',$modele_email->id, $modele_email)->enregistre([
                'sujet_modele' => $sujet_modele,
                'modele' => $modele
            ]);
        }
    }

    public function reprise_donnees_notifications_manuelles(){

        // Reprise de données des notifications manuelles
        $notifications_manuelles = modele('notification_manuelle')->get();
        $documents_notif = modele('notification_manuelle_document')->get()->groupBy('notification_manuelle_id');
        $destinataire_mail = modele('destinataire_notification_mail')->get()->groupBy('notification_manuelle_id');
        $destinataire_erp = modele('destinataire_notification_erp')->get()->groupBy('notification_manuelle_id');

        $tables_libres = Table_libre::get()->pluck('type_element', 'id');

        foreach($notifications_manuelles as $notification_manuelle){

            $type_element_notif = $tables_libres[$notification_manuelle->type_element_id] ?? null;

            $lien_champ_expediteur = null;
            
            if(!empty($notification_manuelle->lien_champ_expediteur)){
                $lien_champ_expediteur = $notification_manuelle->lien_champ_expediteur;

                if(strpos($lien_champ_expediteur,'.') === false)
                    $lien_champ_expediteur = $type_element_notif.'.'.$lien_champ_expediteur;
            }

            if(isset($destinataire_mail[$notification_manuelle->id])){
                foreach($destinataire_mail[$notification_manuelle->id] as $destinataire_mail_item){

                    $filtrages = [];

                    $lien_champ = null;
                    if(!empty($destinataire_mail_item->lien_champ)){
                        $lien_champ = $destinataire_mail_item->lien_champ;

                        if(strpos($lien_champ,'.') === false)
                            $lien_champ = $type_element_notif.'.'.$lien_champ;
                    }
                    else if(!empty($destinataire_mail_item->type_element)){

                        if($destinataire_mail_item->type_element == 'utilisateur'){
                            $lien_champ = 'table_libre|utilisateur/utilisateur.email';
                            $type_element_lien = 'utilisateur';
                        }
                        else{
                            $champ_libre = champ_libre_modele('utilisateur', $destinataire_mail_item->type_element);
                            $lien_champ = 'table_libre|'.$champ_libre->type_element_ajax.'/'.$champ_libre->type_element_ajax.'.id/table_libre|utilisateur.'.$champ_libre->nom_sql.'/utilisateur.email';
                            $type_element_lien = $champ_libre->type_element_ajax;
                        }

                        $filtrages[] = json_encode([
                            'id_cible' => 0,
                            'type_element' => $type_element_lien,
                            'structure' => [
                                    [
                                    'operateur' => 0,
                                    'filtres' => [
                                        [
                                            'nom_sql' => 'id',
                                            'type_element' => $type_element_lien,
                                            'operateur' => 0,
                                            'valeurs' => [$destinataire_mail_item->element_id]
                                        ]
                                    ],
                                    'blocs' => [],
                                    'exclu' => 0
                                    ]
                            ]
                        ]);
                    }


                    management('parametrage_destinataire_email')->enregistre([
                        'notification_manuelle_id' => $notification_manuelle->id,
                        'adresse_mail' => $destinataire_mail_item->adresse_mail,
                        'lien_champ' => $lien_champ,
                        'type' => $destinataire_mail_item->type,
                        'filtrages' => $filtrages
                    ]);
                }
            }

            if(isset($destinataire_erp[$notification_manuelle->id])){
                foreach($destinataire_erp[$notification_manuelle->id] as $destinataire_erp_item){

                    $filtrages = [];

                    $lien_champ = null;
                    $utilisateur_id = null;

                    if(!empty($destinataire_erp_item->lien_champ)){
                        $lien_champ = $destinataire_erp_item->lien_champ;

                        if(strpos($lien_champ,'.') === false)
                            $lien_champ = $type_element_notif.'.'.$lien_champ;
                    }
                    else if(!empty($destinataire_erp_item->type_element)){

                        if($destinataire_erp_item->type_element == 'utilisateur'){
                            $utilisateur_id = $destinataire_erp_item->element_id;
                        }
                        else{
                            $champ_libre = champ_libre_modele('utilisateur', $destinataire_erp_item->type_element);
                            $lien_champ = 'table_libre|'.$champ_libre->type_element_ajax.'/'.$champ_libre->type_element_ajax.'.id/table_libre|utilisateur.'.$champ_libre->nom_sql;
                            $type_element_lien = $champ_libre->type_element_ajax;
                            $filtrages[] = json_encode([
                                'id_cible' => 0,
                                'type_element' => $type_element_lien,
                                'structure' => [
                                        [
                                        'operateur' => 0,
                                        'filtres' => [
                                            [
                                                'nom_sql' => 'id',
                                                'type_element' => $type_element_lien,
                                                'operateur' => 0,
                                                'valeurs' => [$destinataire_erp_item->element_id]
                                            ]
                                        ],
                                        'blocs' => [],
                                        'exclu' => 0
                                        ]
                                ]
                            ]);
                        }
                    }


                    management('parametrage_destinataire_erp')->enregistre([
                        'notification_manuelle_id' => $notification_manuelle->id,
                        'utilisateur_id' => $utilisateur_id,
                        'lien_champ' => $lien_champ,
                        'filtrages' => $filtrages
                    ]);
                }
            }

            if(isset($documents_notif[$notification_manuelle->id])){
                foreach($documents_notif[$notification_manuelle->id] as $document_notif){
                    $piece_jointe = null;
                    $lien_champ = null;

                    if($document_notif->type_fichier == 'nouveau_fichier')
                        $piece_jointe = $document_notif->fichier;
                    else if($document_notif->type_fichier == 'pdf_document')
                        $lien_champ = $type_element_notif.'.pdf';
                    else if($document_notif->type_fichier == 'champ_libre')
                        $lien_champ = $type_element_notif.'.'.$document_notif->nom_fichier;
                    else if($document_notif->type_fichier == 'modele_de_document')
                        $lien_champ = $type_element_notif.'.id/element_table_libre_final|modele_de_document.'.$document_notif->nom_fichier;

                    management('parametrage_piece_jointe_email')->enregistre([
                        'notification_manuelle_id' => $notification_manuelle->id,
                        'piece_jointe' => $piece_jointe,
                        'lien_champ' => $lien_champ,
                    ]);
                }
            }

            $sujet = $notification_manuelle->sujet;
            $contenu = $notification_manuelle->contenu_email;
            $message_notification = $notification_manuelle->message_notification;

            if($notification_manuelle->type_notification == 1){

                $variables = array(
                    '#maintenant#' => '{{#maintenant}}',
                    '#lien_href#' => '{{#url_lien_element}}'
                );

                $champs_libres_element_source = Champ_libre::where('type_element', $type_element_notif)->get();

                foreach($champs_libres_element_source as $champ_libre) {

                    $variables['#'.$champ_libre->nom_sql.'#'] = '{{'.$champ_libre->nom_sql.'}}';
                }

                $message_notification = str_replace(array_keys($variables), array_values($variables), $message_notification);
            }
            else{
                $sujet = $this->transcription_contenu_publipostage($sujet, $type_element_notif);
                $contenu = $this->transcription_contenu_publipostage($contenu, $type_element_notif);
            }

            management('notification_manuelle',$notification_manuelle->id, $notification_manuelle)->enregistre([
                'lien_champ_expediteur' => $lien_champ_expediteur,
                'sujet' => $sujet,
                'contenu_email' => $contenu,
                'message_notification' => $message_notification
            ]);
        }
    }

    public function reprise_donnees_questionnaire(){

        $questionnaires = modele('questionnaire')->get();

        $tables_libres = Table_libre::get()->pluck('type_element', 'id');

        foreach($questionnaires as $questionnaire){

            $type_element_questionnaire = $tables_libres[$questionnaire->type_element_id] ?? null;

            if(empty($type_element_questionnaire))
                continue;

            $sujet = $this->transcription_contenu_publipostage($questionnaire->sujet, $type_element_questionnaire);
            $corps_du_mail = $this->transcription_contenu_publipostage($questionnaire->corps_du_mail, $type_element_questionnaire);

            management('questionnaire',$questionnaire->id, $questionnaire)->enregistre([
                'sujet' => $sujet,
                'corps_du_mail' => $corps_du_mail
            ]);
        }
    }

    public function transcription_contenu_publipostage($texte, $type_element){

        $variables[$type_element] = [
            $type_element
        ];

        $variables_remplacement = [];

        preg_match_all('/\{\{\s*([^{}]+?)\s*\}\}/', $texte, $matches);

        foreach(($matches[1] ?? []) as $variable){
            $type_element_supp = trim(strtok($variable, '['));

            if(empty($type_element_supp) || strpos($type_element_supp, '#') === 0)
                continue;

            $variables[$type_element_supp] = [
                $type_element_supp
            ];
        }

        // Reprise des variables de message de notification email
        foreach($variables as $nom => $info) {

            if(is_object($info) || empty($info))
                continue;

            if(isset($info['type_element']))
                $info[0] = $info['type_element'];
        }

        // on boucle sur le tableau des variables pour remplacer en live les variables par la valeur des champs libres
        foreach($variables as $nom => $info) {

            if(empty($info))
                continue;

            if(is_array($info) && isset($info['type_element']))
                $info[0] = $info['type_element'];

            $champ_libre_lien = null;

            $type_element_variable = $info[0];

            if($type_element_variable != $type_element){

                $champ_libre_lien = Champ_libre::where('type_element', $type_element)
                    ->where('type_element_ajax', $type_element_variable)
                    ->first();

                if(empty($champ_libre_lien))
                    continue;
            }

            if(is_array($info)){
                $champs_libres = table_libre($info[0])->champs_libres()->get();
                $champs_libres_parents = table_libre($info[0])->champs_libres()->whereIn('type', array(42))->get();
            }

            foreach($champs_libres as $champ) {
                $variables_remplacement['{{'.$nom.'['.$champ->nom_sql.']}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'['.$champ->nom_sql.']}}': '{{'.$champ->nom_sql.'}}';
            
                if(in_array($type_element_variable, Variables::$documents_gescom))
                    $variables_remplacement['{{document['.$champ->nom_sql.']}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'['.$champ->nom_sql.']}}': '{{'.$champ->nom_sql.'}}';

            }
            
            // on gère les champs de 2eme niveau
            foreach($champs_libres_parents as $champ_parent) {

                $variables_remplacement['{{'.$nom.'['.$champ_parent->nom_sql.'.id]}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'['.$champ_parent->nom_sql.'[#id]]}}': '{{'.$champ_parent->nom_sql.'[#id]}}';
                
                if(in_array($type_element_variable, Variables::$documents_gescom))
                    $variables_remplacement['{{document['.$champ->nom_sql.'.id]}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'['.$champ_parent->nom_sql.'[#id]]}}': '{{'.$champ_parent->nom_sql.'[#id]}}';

                $champs_libres = table_libre($champ_parent->type_element_ajax)->champs_libres()->get();

                foreach($champs_libres as $champ) {
                    $variables_remplacement['{{'.$nom.'['.$champ_parent->nom_sql.'.'.$champ->nom_sql.']}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'['.$champ_parent->nom_sql.'['.$champ->nom_sql.']]}}': '{{'.$champ_parent->nom_sql.'['.$champ->nom_sql.']}}';

                    if(in_array($type_element_variable, Variables::$documents_gescom))
                        $variables_remplacement['{{document['.$champ_parent->nom_sql.'.'.$champ->nom_sql.']}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'['.$champ_parent->nom_sql.'[#id]]}}': '{{'.$champ_parent->nom_sql.'[#id]}}';

                }
            }

            $variables_remplacement['{{'.$nom.'[lien_vers_element]}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'[#lien_element]}}': '{{#lien_element}}';
            $variables_remplacement['{{'.$nom.'[id]}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'[#id]}}': '{{#id}}';

            if(in_array($type_element_variable, Variables::$documents_gescom)){
                $variables_remplacement['{{document[lien_vers_element]}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'[#lien_element]}}': '{{#lien_element}}';
                $variables_remplacement['{{document[id]}}'] = $champ_libre_lien != null ? '{{'.$champ_libre_lien->nom_sql.'[#id]}}': '{{#id}}';
            }
        }

        $variables_remplacement['#lien_questionnaire#'] = '{{#lien_questionnaire}}';

        $texte = str_replace(array_keys($variables_remplacement), array_values($variables_remplacement), $texte);

        return $texte;
    }
}