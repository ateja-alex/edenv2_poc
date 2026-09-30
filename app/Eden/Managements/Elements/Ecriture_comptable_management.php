<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;

use DB;
use PhpParser\Node\Expr\New_;
use function foo\func;
use Illuminate\Support\Str;

class Ecriture_comptable_management extends Element_management
{

    /**
     *
     * Si jamais le numéro d'écriture comptable ne doit pas commencer à 1 (à surcharger en spécifique)
     *
     */
    public function increment_initial_ecriture_comptable()
    {

        return 0;
    }

    /**
     *
     * @cf description sur Element_management
     *
     * On ajoute le champ entite_id
     *
     */
    protected function retraite_modifications($modifications)
    {

        // on arrondit
        if (isset($modifications['debit']))
            $modifications['debit'] = round($modifications['debit'], 2);

        if (isset($modifications['credit']))
            $modifications['credit'] = round($modifications['credit'], 2);

        // il faut vérifier qu'on ait une entité de renseignée
        if (!empty($modifications['entite_id']))
            return parent::retraite_modifications($modifications);

        // est ce qu'on a une seule entité existente ?
        $nombre_entites = modele('entite')->count();

        if ($nombre_entites == 1) {

            $entite = modele('entite')->first();

            $modifications['entite_id'] = $entite->id;

            return parent::retraite_modifications($modifications);
        }

        return traduction('messages.php.ecriture_comptable.entite_non_renseigne');
    }

    /**
     *
     * Exporte les écritures au format FEC
     *
     */
    public function exporte_ecritures_fec($ecritures, $parametres)
    {

        $ligne = array();

        $ligne[] = 'JournalCode';
        $ligne[] = 'JournalLib';
        $ligne[] = 'EcritureNum';
        $ligne[] = 'EcritureDate';
        $ligne[] = 'CompteNum';
        $ligne[] = 'CompteLib';
        $ligne[] = 'CompAuxNum';
        $ligne[] = 'CompAuxLib';
        $ligne[] = 'PieceRef';
        $ligne[] = 'PieceDate';
        $ligne[] = 'EcritureLib';
        $ligne[] = 'Debit';
        $ligne[] = 'Credit';
        $ligne[] = 'EcritureLet';
        $ligne[] = 'DateLet';
        $ligne[] = 'ValidDate';
        $ligne[] = 'Montantdevise';
        $ligne[] = 'Idevise';

        $contenu_fichier[] = implode("\t", $ligne);

        foreach ($ecritures as $ecriture) {

            $ecriture_management = management('ecriture_comptable', $ecriture->id);

            $ecriture_management->enregistre_modele(array('exporte' => 1));

            $journal = modele('journal_comptable', $ecriture->journal_id);
            $compte = modele('compte_comptable', $ecriture->compte_comptable_id);

            $ligne = array();

            // 1. Le code journal de l'écriture comptable	ecriture_comptable.journal_id.code_journal	JournalCode
            $ligne[] = $journal->code_journal;

            // 2. Le libellé journal de l'écriture comptable	ecriture_comptable.journal_id. nom	JournalLib
            $ligne[] = $journal->nom;

            // 3. Le numéro de séquence de l'écriture comptable	ecriture_comptable.ecriture_id	EcritureNum
            $ligne[] = $ecriture->ecriture_id;

            // 4. La date de comptabilisation de l'écriture comptable	ecriture_comptable.date	EcritureDate
            $ligne[] = formate_date('Ymd', $ecriture->date);

            // 5. Le numéro de compte	ecriture_comptable .compte_comptable_id.numero_de_compte	CompteNum
            $ligne[] = $compte->numero_de_compte;

            // 6. Le libellé de compte	ecriture_comptable .compte_comptable_id.libelle	CompteLib
            $ligne[] = $compte->libelle;

            // 7. Le numéro de compte auxiliaire	ecriture_comptable.auxiliaire	CompAuxNum
            $ligne[] = $ecriture->auxiliaire;

            // 8. Le libellé de compte auxiliaire	ecriture_comptable.auxiliaire	CompAuxLib
            $ligne[] = $ecriture->auxiliaire;

            // 9. La référence de la pièce justificative	ecriture_comptable.reference_document	PieceRef
            $ligne[] = $ecriture->reference_document;

            // 10. La date de la pièce justificative	ecriture_comptable.date	PieceDate
            $ligne[] = formate_date('Ymd', $ecriture->date);

            // 11. Le libellé de l'écriture comptable	ecriture_comptable.libelle	EcritureLib
            $ligne[] = $ecriture->libelle;

            // 12. Le montant au débit	ecriture_comptable.debit	Debit
            $ligne[] = $ecriture->debit;

            // 13. Le montant au crédit	ecriture_comptable.credit	Credit
            $ligne[] = $ecriture->credit;

            // 14. Le lettrage de l'écriture comptable	VIDE	EcritureLet
            $ligne[] = "";

            // 15. La date de lettrage	VIDE	DateLet
            $ligne[] = "";

            // 16. La date de validation de l'écriture comptable	ecriture_comptable.date	ValidDate
            $ligne[] = formate_date('Ymd', $ecriture->date);

            // 17. Le montant en devise	VIDE	Montantdevise
            $ligne[] = "";

            // 18. L'identifiant de la devise	VIDE	Idevise
            $ligne[] = "";

            $contenu_fichier[] = implode("\t", $ligne);

        }

        $contenu_fichier = implode("\n", $contenu_fichier);

        // on crée le fichier
        $fichier = 'export_compta_' . management('entite', $parametres['entite_id'])->modele->nom . '_' . formate_date('Ymd', $parametres['date_debut']) . '_' . formate_date('Ymd', $parametres['date_fin']) . '.txt';
        
        \Storage::put($fichier, $contenu_fichier);

        $headers = array();

        return response()->download(storage_path('app/' . $fichier), $fichier, $headers);
    }

    /**
     * @param $colonne_par_champ_nom_sql
     * @param $elements
     * @param $select
     * @param $informations_parent
     * @param $managements_champs
     * @return void
     *
     * Gère les tables liées pour les exports
     *
     */
    public function gestion_requete_sous_table($colonne_par_champ_nom_sql,$elements,&$select,$informations_parent,&$managements_champs){

        if(isset($colonne_par_champ_nom_sql['sous_table']['document'])){

            $types_elements_possibles = (clone $elements)
                ->select('type_element')
                ->groupBy('type_element')
                ->whereNotNull('type_element')
                ->get()->pluck('type_element')->toArray();

            foreach($types_elements_possibles as $type_element_lien) {

                $compteur_join = $this->compteur_join;

                $alias_table = 'tj' . $compteur_join;

                $elements->leftJoin(DB::raw($type_element_lien . ' as ' . $alias_table),function($join) use ($informations_parent,$alias_table,$type_element_lien){
                    $join->on($informations_parent['alias_table'] . '.element_id', $alias_table . '.id')
                        ->where($informations_parent['alias_table'].'.type_element', $type_element_lien);
                });

                $this->compteur_join++;

                $informations_parent_enfant = array(
                    'alias_table' => $alias_table,
                    'type_element' => $type_element_lien
                );

                $this->gestion_requete_sous_table($colonne_par_champ_nom_sql['sous_table']['document'], $elements, $select, $informations_parent_enfant, $managements_champs);
            }
        }

        parent::gestion_requete_sous_table($colonne_par_champ_nom_sql,$elements,$select,$informations_parent,$managements_champs);
    }

    /**
     * @param $parametres
     *
     * Récupère le nom du fichier lors d'un export
     *
     */
    public function nom_fichier_export($management_modele,$parametres){

        $nom_fichier = $management_modele->modele->nom . '_' . $management_modele->champ('entite_id')->affiche() . '_' . formate_date('Ymd', $parametres['date_debut']) . '_' . formate_date('Ymd', $parametres['date_fin']);

        return str_replace(' ', '_', mb_strtolower(Str::ascii($nom_fichier)));
    }
}
