<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class S20260810_reprise_type_element_source_acomptes_devis_achat implements Script {

    // pour chaque type de document à rattraper : le type de document source à privilégier au sein du groupe,
    // et si on a le droit de se rabattre sur n'importe quel autre document du groupe à défaut
    protected $correspondances = [

        'acompte_vente' => ['type_source_privilegie' => 'commande_vente', 'repli_sur_groupe' => true],
        'acompte_achat' => ['type_source_privilegie' => 'commande_achat', 'repli_sur_groupe' => true],
        'devis_achat' => ['type_source_privilegie' => 'commande_vente', 'repli_sur_groupe' => false],
    ];

    public function execute(){

        // l'ancien système de liaison entre documents (avant les transformations à la ligne) a pu être supprimé entre temps
        if(!Schema::hasTable('lien_entre_document'))
            return true;

        // on ne récupère que les groupes de documents contenant au moins un des types de documents ciblés
        $liens = DB::table('lien_entre_document')
            ->whereIn('id_groupe_document', function($requete){

                $requete->select('id_groupe_document')
                    ->from('lien_entre_document')
                    ->whereIn('type_element', array_keys($this->correspondances));
            })
            ->get();

        $documents_par_groupe = [];

        foreach($liens as $lien){

            if(empty($lien->id_groupe_document))
                continue;

            $documents_par_groupe[$lien->id_groupe_document][] = [
                'type_element' => $lien->type_element,
                'id' => $lien->id_document,
            ];
        }

        foreach($this->correspondances as $type_element => $correspondance){

            $table_lignes = $type_element.'_lignes';

            if(!Schema::hasTable($table_lignes) || !Schema::hasColumns($table_lignes, ['type_element_source', 'id_element_source', 'id_ligne_source']))
                continue;

            foreach($documents_par_groupe as $documents){

                $documents_a_rattraper = array_filter($documents, function($document) use ($type_element){

                    return $document['type_element'] == $type_element;
                });

                if(empty($documents_a_rattraper))
                    continue;

                $document_source = $this->trouve_document_source($documents, $type_element, $correspondance);

                // pas de document source trouvé dans le groupe (et pas de repli autorisé) : on ne touche à rien
                if($document_source === null)
                    continue;

                // on indexe les lignes du document source par article, pour pouvoir rattacher chaque ligne à la bonne ligne source
                $lignes_source_par_article = [];

                $lignes_source = modele($document_source['type_element'].'_lignes')
                    ->where('document_id', $document_source['id'])
                    ->orderBy('id')
                    ->get();

                foreach($lignes_source as $ligne_source){

                    if(empty($ligne_source->article_id))
                        continue;

                    if(!isset($lignes_source_par_article[$ligne_source->article_id]))
                        $lignes_source_par_article[$ligne_source->article_id] = $ligne_source->id;
                }

                foreach($documents_a_rattraper as $document){

                    $lignes_a_rattraper = modele($table_lignes)
                        ->where('document_id', $document['id'])
                        ->where(function($requete){

                            $requete->whereNull('type_element_source')->orWhere('type_element_source', '');
                        })
                        ->get();

                    foreach($lignes_a_rattraper as $ligne){

                        $donnees = [
                            'type_element_source' => $document_source['type_element'],
                            'id_element_source' => $document_source['id'],
                        ];

                        // si on trouve une ligne avec le même article dans le document source, on rattache également la ligne source
                        // sinon on se contente du document source, sans ligne précise
                        if(!empty($ligne->article_id) && isset($lignes_source_par_article[$ligne->article_id]))
                            $donnees['id_ligne_source'] = $lignes_source_par_article[$ligne->article_id];

                        modele($table_lignes)->where('id', $ligne->id)->update($donnees);
                    }
                }
            }
        }

        return true;
    }

    /**
     *
     * Retrouve, au sein d'un groupe de documents, le document source à utiliser pour rattacher les lignes d'un document de type $type_element
     *
     */
    protected function trouve_document_source($documents, $type_element, $correspondance){

        foreach($documents as $document){

            if($document['type_element'] == $correspondance['type_source_privilegie'])
                return $document;
        }

        if($correspondance['repli_sur_groupe'] !== true)
            return null;

        foreach($documents as $document){

            if($document['type_element'] == $type_element)
                continue;

            return $document;
        }

        return null;
    }
}
