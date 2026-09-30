<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Compta_v2_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs()
    {
        $types_modele = ['journal_comptable', 'compte_comptable', 'mode_paiement', 'compte_bancaire'];

        foreach ($types_modele as $type_modele) {
			
			
            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

        $valeurs['compte_comptable'] = modele('compte_comptable')->select(\DB::raw("CONCAT(numero_de_compte,' ',libelle) as nom, id"))->get()->pluck('nom', 'id')->toArray();

        return $this->fonctionnalites($valeurs);

    }

    /*
     *
     * Retour les fonctionnalitées du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $fonctionnalites = array(

            // saisie des documents
            'Paramètres généraux' => array(

                array(

                    'nom' => "Journal de vente",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['journal_comptable']) ? $valeurs['journal_comptable'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compta_journal_vente",
                ),
                array(

                    'nom' => "Journal d'achat",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['journal_comptable']) ? $valeurs['journal_comptable'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compta_journal_achat",
                ),
                array(

                    'nom' => "Journal notes de frais",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['journal_comptable']) ? $valeurs['journal_comptable'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compta_journal_note_de_frais",
                ),
                array(

                    'nom' => "Compte général clients",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_comptable']) ? $valeurs['compte_comptable'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compta_compte_general_clients",
                ),
                array(

                    'nom' => "Compte général fournisseurs",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_comptable']) ? $valeurs['compte_comptable'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compta_compte_general_fournisseurs",
                ),
                array(

                    'nom' => "Compte général salariés",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_comptable']) ? $valeurs['compte_comptable'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compta_compte_general_salaries",
                ),
                array(

                    'nom' => "Nombre de caractères pour les comptes auxiliaires",
                    'type' => "input",
                    'placeholder' => "Saisissez un nombre de caractères",
                    'description' => "",
                    'fonctionnalite' => "compta_compte_auxiliaire_nombre_caracteres",
                ),
                array(

                    'nom' => "Générer des paiements via les avoirs",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['mode_paiement']) ? $valeurs['mode_paiement'] : array(),
                    'description' => "",
					'valeur_vide' => true,
                    'fonctionnalite' => "compta_generation_paiement_via_avoirs",
                ),
                array(

                    'nom' => "Choix de la banque pour la génération de paiement via les avoirs",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_bancaire']) ? $valeurs['compte_bancaire'] : array(),
                    'description' => "",
					'valeur_vide' => true,
                    'fonctionnalite' => "compta_banque_defaut_generation_paiement_via_avoir",
                ),
                array(

                    'nom' => "ID de l'article pour la déduction de l'acompte sur les factures",
                    'type' => "input",
                    'description' => "Si vous vouhaitez que l'acompte vienne en déduction du HT de la facture, renseignez ici l'id de l'article à utiliser",
                    'fonctionnalite' => "compta_article_id_pour_acompte",
                    'valide' => true,
                ),
                array(
                    'nom' => "Remise vente",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_comptable']) ? $valeurs['compte_comptable'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compte_comptable_remise_vente",
                ),
                array(

                    'nom' => "Remise achat",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_comptable']) ? $valeurs['compte_comptable'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compte_comptable_remise_achat",
                ),

                array(

                    'nom' => "Libellé pour les paiements",
                    'type' => "publipostage_texte",
                    'type_element_publipostage' => 'paiement',
                    'description' => "",
                    'fonctionnalite' => "compta_libelle_paiement",
					'valide' => true
                ),
                array(

                    'nom' => "Libellé pour les notes de frais",
                    'type' => "publipostage_texte",
                    'type_element_publipostage' => 'note_de_frais',
                    'description' => "",
                    'fonctionnalite' => "compta_libelle_note_de_frais",
					'valide' => true
                ),
				array(

					'nom' => "Comptabiliser automatiquement",
					'type' => "badge_multiselection",
					'valeurs_badges' => array(
						
						'paiement' => 'Paiements',
						'facture_vente' => 'Factures clients',
						'avoir_vente' => 'Avoirs clients',
                        'facture_achat' => 'Facture achat',
                        'avoir_achat' => 'Avoir achat',
                        'note_de_frais' => 'Note de frais',
					),
					'fonctionnalite' => "comptabiliser_automatiquement",
				),
				
            ),
            'Recouvrement' => array(
                array(

                    'nom' => "Recouvrement sur les fiches clients",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "recouvrement",
                ),
                array(

                    'nom' => "Recouvrement (sans groupe) : ne pas afficher les factures relancées depuis moins de X jours",
                    'type' => "input",
                    'placeholder' => "Saisissez un %",
                    'description' => "",
                    'fonctionnalite' => "recouvrement_ne_pas_afficher_les_factures_relancees_depuis_x_jours",
                ),
            ),
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Comptabilité';
    }

    public function obtenir_nom_module(){

        return 'Module de comptabilité';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/euro.png';
    }
}