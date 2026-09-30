<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;
use App\Eden\Champs\Champ;

class Facturation_electronique_cycle_de_vie_management extends Element_management {

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'relancer_envoi';

        return $liste_options;
    }

    public function motifs_statut() {

        return [
            206 => ['AUTRE', 'CMD_ERR', 'SIRET_ERR', 'CODE_ROUTAGE_ERR', 'REF_CT_ABSENT', 'REF_ERR', 'PU_ERR', 'REM_ERR', 'QTE_ERR', 'ART_ERR', 'MODPAI_ERR', 'QUALITE_ERR', 'LIVR_INCOMP'],
            207 => ['AUTRE', 'COORD_BANC_ERR', 'TX_TVA_ERR', 'MONTANTTOTAL_ERR', 'CALCUL_ERR', 'NON_CONFORME', 'DOUBLON', 'DEST_ERR', 'TRANSAC_INC', 'EMMET_INC', 'CONTRAT_TERM', 'DOUBLE_FACT', 'CMD_ERR', 'ADR_ERR', 'SIRET_ERR', 'CODE_ROUTAGE_ERR', 'REF_CT_ABSENT', 'REF_ERR', 'PU_ERR', 'REM_ERR', 'QTE_ERR', 'ART_ERR', 'MODPAI_ERR', 'QUALITE_ERR', 'LIVR_INCOMP'],
            208 => ['JUSTIF_ABS', 'COORD_BANC_ERR', 'CMD_ERR', 'SIRET_ERR', 'CODE_ROUTAGE_ERR', 'REF_CT_ABSENT', 'REF_ERR'],
            210 => ['TX_TVA_ERR', 'MONTANTTOTAL_ERR', 'CALCUL_ERR', 'NON_CONFORME', 'DOUBLON', 'DEST_ERR', 'TRANSAC_INC', 'EMMET_INC', 'CONTRAT_TERM', 'DOUBLE_FACT', 'CMD_ERR', 'ADR_ERR', 'REF_CT_ABSENT'],
        ];
    }

    public function methodes_post_modification($modele, $modele_avant, $modifications) {

        if(empty($modele->fichier_cdar))
            $this->genere_fichier_cdar($modele);

        return parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    private function genere_fichier_cdar($modele) {

        if(!empty($modele->facturation_electronique_achat_id)) {

            $achat = management('facturation_electronique_achat', $modele->facturation_electronique_achat_id);

            $entite_management = !empty($achat->modele->entite_id) ? management('entite', $achat->modele->entite_id) : null;

            $donnees = [
                'role_emetteur_cdar' => 'BY',
                'siren_emetteur_cdar' => $entite_management ? substr($entite_management->modele->siret ?? '', 0, 9) : $achat->modele->siren_destinataire,
                'nom_emetteur_cdar' => $entite_management ? $entite_management->champ('nom')->affiche() : null,
                'reference_document' => $achat->modele->reference_document,
                'date_document' => $achat->modele->date_document,
                'type_code_facturx' => $achat->modele->type_code_facturx,
                'statut_code' => $modele->statut_achat,
                'role_destinataire_cdar' => 'SE',
                'siren_destinataire_cdar' => $achat->modele->siren_emetteur,
                'siren_vendeur_facture' => $achat->modele->siren_emetteur,
            ];

            $tracking_id = $achat->modele->flow_id;
        }
        elseif(!empty($modele->facture_vente_id) || !empty($modele->avoir_vente_id)) {

            $type_element_vente = !empty($modele->facture_vente_id) ? 'facture_vente' : 'avoir_vente';

            $vente = management($type_element_vente, $modele->facture_vente_id ?? $modele->avoir_vente_id);

            $entite_management = !empty($vente->modele->entite_id) ? management('entite', $vente->modele->entite_id) : null;
            $client_management = !empty($vente->modele->client_id) ? management('client', $vente->modele->client_id) : null;

            $siren_nous = $entite_management ? substr($entite_management->modele->siret ?? '', 0, 9) : null;

            $donnees = [
                'role_emetteur_cdar' => 'SE',
                'siren_emetteur_cdar' => $siren_nous,
                'nom_emetteur_cdar' => $entite_management ? $entite_management->champ('nom')->affiche() : null,
                'reference_document' => $vente->modele->reference_document,
                'date_document' => $vente->modele->date,
                'type_code_facturx' => $type_element_vente == 'avoir_vente' ? 381 : 380,
                'statut_code' => $modele->statut_vente,
                'role_destinataire_cdar' => 'BY',
                'siren_destinataire_cdar' => $client_management->modele->siren ?? null,
                'siren_vendeur_facture' => $siren_nous,
            ];

            $tracking_id = $vente->modele->facturation_electronique_flow_id;

            if($modele->statut_vente == 212)
                $reste_a_payer = $this->reste_a_payer($type_element_vente, $vente->modele);
        }
        else {

            return;
        }

        $donnees['message_id'] = (string) \Illuminate\Support\Str::uuid();
        $donnees['motif'] = $modele->motif;
        $donnees['motif_code'] = $modele->motif_code;
        $donnees['statut_label'] = Champ::recuperer_valeur_listes_preenregistrees(729)['liste'][$donnees['statut_code']] ?? $donnees['statut_code'];
        $donnees['encaissements_par_taux'] = json_decode($modele->encaissements_par_taux ?? '[]', true) ?? [];
        $donnees['reste_a_payer'] = $reste_a_payer ?? null;
        $donnees['date_evenement'] = $modele->date_evenement ?? date('Y-m-d');

        $xml = view('eden::pdf.include.facture_x.xml_cdar', $donnees)->render();

        $nom_fichier = \Illuminate\Support\Str::random(40).'.xml';

        \Storage::disk('public')->put($nom_fichier, $xml);

        $this->enregistre_modele(['fichier_cdar' => $nom_fichier, 'tracking_id' => $tracking_id]);
    }

    /**
     *
     * Reste à payer sur le document, une fois pris en compte tous les encaissements déclarés
     *
     */
    public function reste_a_payer($type_element_vente, $document) {

        $deja_encaisse = collect($this->encaissements_deja_declares($type_element_vente, $document->id))->sum();

        return max(0, round($document->montant_document_ttc - $deja_encaisse, 2));
    }

    /**
     *
     * Montants déjà encaissés sur le document, indexés par taux de TVA
     *
     */
    public function encaissements_deja_declares($type_element_vente, $id_document) {

        $lignes = modele('facturation_electronique_cycle_de_vie')
            ->where($type_element_vente.'_id', $id_document)
            ->where('statut_vente', 212)
            ->where(function($requete) {
                $requete->whereNull('statut_envoi')->orWhere('statut_envoi', '!=', 3);
            })
            ->where(function($requete) {
                $requete->whereNull('inactif')->orWhere('inactif', 0);
            })
            ->get();

        $encaissements = [];

        foreach($lignes as $ligne) {

            foreach(json_decode($ligne->encaissements_par_taux ?? '[]', true) ?? [] as $encaissement) {

                $taux = (string) ($encaissement['taux'] ?? 0);

                $encaissements[$taux] = round(($encaissements[$taux] ?? 0) + ($encaissement['montant'] ?? 0), 2);
            }
        }

        return $encaissements;
    }

}
