<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaires_champs;
use Illuminate\Support\Facades\Schema;
use DB;

class S20260521_modifications_champs_divers implements Script{
    public function execute(){

        $nouvelles_informations_seuil_article = [
            'type' => 42,
            'recherche' => 1,
			'type_element_ajax' => "conditionnement",
			'filtres' => [
				'conditionnement' => array (
					array (
						'operateur' => 0,
						'exclu' => 0,
						'blocs' => array (),
						'filtres' => 
						array (
							array (
								'type_element' => 'conditionnement',
								'champ_liaison' => NULL,
								'valeurs' => 'lien_champ|seuil_article.article_id',
								'nom_sql' => 'article_id',
								'operateur' => 0,
							),
						),
					),
				), 
			],
        ];

        $nouvelles_informations_transformation_stocks = [
            'type' => 42,
            'recherche' => 1,
            'type_element_ajax' => "conditionnement",
            'filtres' => [
                'conditionnement' => array (
                    array (
                        'operateur' => 0,
                        'exclu' => 0,
                        'blocs' => array (),
                        'filtres' => 
                        array (
                            array (
                                'type_element' => 'conditionnement',
                                'champ_liaison' => NULL,
                                'valeurs' => 'lien_champ|transformation_stocks.article_id',
                                'nom_sql' => 'article_id',
                                'operateur' => 0,
                            ),
                        ),
                    ),
                ), 
            ],
        ];

        $nouvelles_informations_transformation_stocks_lignes = [
            'type' => 42,
            'recherche' => 1,
            'type_element_ajax' => "conditionnement",
            'filtres' => [
                'conditionnement' => array (
                    array (
                        'operateur' => 0,
                        'exclu' => 0,
                        'blocs' => array (),
                        'filtres' => 
                        array (
                            array (
                                'type_element' => 'conditionnement',
                                'champ_liaison' => NULL,
                                'valeurs' => 'lien_champ|transformation_stocks_lignes.transformation_stocks_id/transformation_stocks.article_id',
                                'nom_sql' => 'article_id',
                                'operateur' => 0,
                            ),
                        ),
                    ),
                ), 
            ],
        ];

        $seuil_article_champ = Champ_libre::where('type_element', 'seuil_article')
            ->where('nom_sql', 'conditionnement_id')
            ->get();

        $transformation_stocks_champ = Champ_libre::where('type_element', 'transformation_stocks')
            ->where('nom_sql', 'conditionnement_depart_id')
            ->get();

        $transformation_stocks_lignes_champ = Champ_libre::where('type_element', 'transformation_stocks_lignes')
            ->where('nom_sql', 'conditionnement_id')
            ->get();

         if(Schema::hasColumn('transformation_stocks', 'conditionnement_depart')){
            Champ_libre::where('type_element', 'transformation_stocks')->whereIn('nom_sql', ['conditionnement_depart'])->delete();
            Schema::dropColumns('transformation_stocks', ['conditionnement_depart']);
        }

        if(Schema::hasColumn('transformation_stocks_lignes', 'conditionnement')){
            DB::statement('UPDATE transformation_stocks_lignes SET conditionnement_id = ROUND(conditionnement) WHERE conditionnement IS NOT NULL');
            Champ_libre::where('type_element', 'transformation_stocks_lignes')->whereIn('nom_sql', ['conditionnement'])->delete();
            Schema::dropColumns('transformation_stocks_lignes', ['conditionnement']);
        }

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations_seuil_article, $seuil_article_champ, true);
        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations_transformation_stocks, $transformation_stocks_champ, true);
        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations_transformation_stocks_lignes,$transformation_stocks_lignes_champ,true);
       
        return true;
    }
}