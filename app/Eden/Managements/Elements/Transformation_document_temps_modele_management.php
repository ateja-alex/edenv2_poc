<?php

namespace App\Eden\Managements\Elements;


use App\Eden\Champs\Champ;
use App\Eden\Champs\Champ_date;
use App\Eden\Models\Elements\Article;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;

class Transformation_document_temps_modele_management extends Element_management {

    public function calcul_utilisateurs_concernes($parametres){

        $this->recuperation_dates($parametres);

        $requete = $this->requete_base_feuille_de_temps($parametres);

        $utilisateurs_a_transformer = $requete
            ->select('utilisateur.*')
            ->join('utilisateur','utilisateur.id','feuille_de_temps.utilisateur_id')
            ->groupBy('utilisateur.id')->get();

        foreach($utilisateurs_a_transformer as $utilisateur){
            $utilisateur->chaine_affichage = management('utilisateur',$utilisateur->id,$utilisateur)->affiche();
        }

        return $utilisateurs_a_transformer;
    }

    public function donnees_pour_transformation($parametres){

        $this->recuperation_dates($parametres);

        $requete = $this->requete_base_feuille_de_temps($parametres);

        $feuille_de_temps_a_transformer = $requete
            ->join('article','article.id','activite.article_id')
            ->whereIn('feuille_de_temps.utilisateur_id',$parametres['utilisateurs_a_transformer'])
            ->groupBy('feuille_de_temps.activite_id');

        $colonnes_articles = DB::getSchemaBuilder()->getColumnListing('article');

        $json_article = collect($colonnes_articles)->map(function ($colonne) {
            return '"' . $colonne . '", article.' . $colonne;
        })->implode(', ');

        $selects = [
            'feuille_de_temps.activite_id',
            'activite.tarif',
            'activite.remise_pourcentage',
            'activite.activite as titre_activite',
            'fdc.commentaire as commentaire',
            DB::raw("SUM(feuille_de_temps.duree) as temps"),
            DB::raw("GROUP_CONCAT(feuille_de_temps.id) as ids_feuilles"),
            DB::raw('JSON_OBJECT(' . $json_article . ') as article')
        ];

        if(fiche('saisie_des_temps')->structure_fiche()['options']['unite'] == 'heure')
            $selects[] = DB::raw("SUM(feuille_de_temps.duree) as temps");
        else
            $selects[] = DB::raw("SUM(feuille_de_temps.duree_jours) as temps");

        if($this->modele->niveau_detail >= 1){
            $feuille_de_temps_a_transformer->groupBy('feuille_de_temps.utilisateur_id');
            $selects[] = 'feuille_de_temps.utilisateur_id';
        }

        if($this->modele->niveau_detail == 2) {
            $feuille_de_temps_a_transformer->groupBy('feuille_de_temps.date');
            $selects[] = DB::raw("DATE_FORMAT(feuille_de_temps.date,'%d/%m/%Y') as date");
        }
        else
            $selects[] = DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT(feuille_de_temps.date,'%d/%m/%Y') SEPARATOR ', ') as date");

        $feuille_de_temps_a_transformer = $feuille_de_temps_a_transformer->select($selects)->get();

        $modeles_articles = Article::hydrate(array_map(function($article) {
                return json_decode($article);
            },$feuille_de_temps_a_transformer->pluck('article')->toArray())
        );

        $type_document = $this->type_document();

        $management_document= management($type_document);

        $donnees_document = $this->informations_origine($parametres);

        $articles = [];

        foreach($feuille_de_temps_a_transformer as $index => $feuille_de_temps){

            $modele_article = $modeles_articles[$index];

            $informations_article = $management_document->article_pour_document(
                $modele_article->id,
                [
                    'client_id' => $donnees_document['client_id'] ?? false,
                    'fournisseur_id' => $donnees_document['fournisseur_id'] ?? false,
                    'date_document' => $donnees_document['date'] ?? date('Y-m-d'),
                ],
                $modele_article
            );

            $ligne = modele($type_document.'_lignes');

            $ligne->modele = $informations_article;
            $ligne->quantite = $feuille_de_temps->temps;
            $ligne->article_id = $informations_article->id;
            $ligne->designation = !empty($feuille_de_temps->titre_activite) ? $feuille_de_temps->titre_activite : $informations_article->designation;
            $ligne->tarif = !empty($feuille_de_temps->tarif) ? $feuille_de_temps->tarif : $informations_article->tarif;
            $ligne->remise = !empty($feuille_de_temps->remise_pourcentage) ? $feuille_de_temps->remise_pourcentage : 0;
            $ligne->prix_achat = $informations_article->prix_achat;
            $ligne->tva = $informations_article->taux_de_tva;
            $ligne->conditionnement = 0;
            $ligne->achats = [];
            $ligne->feuille_de_temps_ids  = explode(',',$feuille_de_temps->ids_feuilles);

            if(!empty($feuille_de_temps->utilisateur_id))
                $ligne->utilisateur_id = $feuille_de_temps->utilisateur_id;

            if($this->modele->dates_commentaires_lignes)
                $ligne->description = ($this->modele->niveau_detail == 2 ?
                        traduction('messages.php.transformation_document_temps_modele.date_feuille') : traduction('messages.php.transformation_document_temps_modele.dates_feuilles'))
                    .' : '.$feuille_de_temps->date;

            if(!empty($feuille_de_temps->commentaire))
                $ligne->description .= (!empty($ligne->description) ? "<br>" : '').$feuille_de_temps->commentaire;

            $articles[] = $ligne;
        }

        $donnees_document['articles'] = collect($articles);

        return $donnees_document;
    }

    public function type_document(){

        $type_document = Table_libre::where('id',$this->modele->type_document)->value('nom_table_sql');

        return $type_document;
    }

    public function informations_origine($parametres){

        $element_management = management($parametres['type_element'],$parametres['element_id']);

        $champs_transformation = modele('transformation_document_temps_mappage')
            ->where('modele_id',$this->modele->id)
            ->get();

        $donnees_document = [];

        foreach($champs_transformation as $champ){

            if($champ->champ_dur == 1) {

                $valeur = $champ->champ_source;

                if(strpos($valeur,'#/date_debut#') !== false)
                    $valeur = str_replace('#/date_debut#',date('d/m/y',strtotime($parametres['date_debut'])),$valeur);

                if(strpos($valeur,'#/date_fin#') !== false)
                    $valeur = str_replace('#/date_fin#',date('d/m/y',strtotime($parametres['date_fin'])),$valeur);

                $valeur = $element_management->recupere_texte_a_afficher($valeur);
            }
            else
                $valeur = $element_management->modele->{$champ->champ_source};

            $donnees_document[$champ->champ_destination] = $valeur;
        }

        return $donnees_document;
    }

    public function recuperation_dates(&$parametres){

        $dates = $parametres['dates'];

        $date_debut = null;
        $date_fin = null;

        $champ_libre = champ_libre_modele('feuille_de_temps','date');

        if(!empty($dates['variable']))
            list($date_debut, $date_fin) = (new Champ_date($champ_libre))->transforme_variable_date($dates['variable']);

        $parametres['date_debut'] = date('Y-m-d', strtotime(str_replace('/', '-', ($dates['debut'] ?? $date_debut))));
        $parametres['date_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace('/', '-', ($dates['fin'] ?? $date_fin ?? date('Y-m-d 23:59:59')))));
    }

    public function requete_base_feuille_de_temps($parametres){

        return modele('feuille_de_temps')
            ->where('feuille_de_temps.type_element',$parametres['type_element'])
            ->where('feuille_de_temps.element_id',$parametres['element_id'])
            ->join('activite','activite.id','feuille_de_temps.activite_id')
            ->leftJoin('feuille_de_temps_commentaire as fdc', function($join) {
                $join->on('fdc.type_element', 'feuille_de_temps.type_element')
                    ->on('fdc.element_id', 'feuille_de_temps.element_id')
                    ->on('fdc.activite_id', 'feuille_de_temps.activite_id')
                    ->on('fdc.utilisateur_id', 'feuille_de_temps.utilisateur_id')
                    ->on('fdc.date', 'feuille_de_temps.date')
                    ->where('fdc.mode_affichage',1);
            })
            ->whereBetween('feuille_de_temps.date',[$parametres['date_debut'],$parametres['date_fin']])
            ->join('feuille_de_temps_periode_validee',function($join){
                $join->on('feuille_de_temps_periode_validee.utilisateur_id','feuille_de_temps.utilisateur_id')
                    ->on('feuille_de_temps.date','>=','feuille_de_temps_periode_validee.date_debut')
                    ->on('feuille_de_temps.date','<=','feuille_de_temps_periode_validee.date_fin')
                    ->where(DB::raw('COALESCE(feuille_de_temps_periode_validee.inactif,0)'),0);
            })
            ->whereNull('type_element_transforme')
            ->whereNull('element_id_transforme')
            ->orderBy('feuille_de_temps.date','asc');
    }
}