<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;
use DB;
/**
 * Gestion des rapports
 */
class Mouvement_numero_de_serie_management extends Rapports_management
{

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
    /**
     *
     * On calcule le CA par mois
     *
     */
    public function genere($ajax = false)
    {
        
        $dates = $this->dates_mensuelles($this->rapport);

        $fournisseur = $this->fournisseur($this->rapport);

        $this->export_excel($this->rapport);

        $bl_ventes = modele('bl_vente')
            ->join('bl_vente_lignes', 'bl_vente_lignes.document_id', 'bl_vente.id')
            ->join('article_fournisseur', 'article_fournisseur.article_id', 'bl_vente_lignes.article_id')
            ->select('*',DB::raw('@type_element := "bl_vente" as type_element'),DB::raw('@quantite := -quantite as quantite'))
            ->where('numero_de_serie', '!=', '')
            ->where('date', '>=', $dates['date_debut'])->where('date', '<=', $dates['date_fin'] . ' 23:59:59');

        $bl_achats = modele('bl_achat')
            ->join('bl_achat_lignes', 'bl_achat_lignes.document_id', 'bl_achat.id')
            ->select('*',DB::raw('@type_element := "bl_achat" as type_element'))
            ->where('numero_de_serie', '!=', '')
            ->where('date', '>=', $dates['date_debut'])->where('date', '<=', $dates['date_fin'] . ' 23:59:59');

        if ($fournisseur != 'null') {
            $bl_ventes = $bl_ventes->where('fournisseur_id', $fournisseur);
            $bl_achats = $bl_achats->where('fournisseur_id', $fournisseur);
        }

        $bl_ventes = $bl_ventes->get()->keyBy('fournisseur_id')->toArray();
        $bl_achats = $bl_achats->get()->keyBy('fournisseur_id')->toArray();

        $bls_par_fournisseur=array();

        foreach($bl_ventes as $fournisseur_id => $bl_vente){

            $bls_par_fournisseur[$fournisseur_id][] = $bl_vente;
        }

        foreach($bl_achats as $fournisseur_id => $bl_achat){

            $bls_par_fournisseur[$fournisseur_id][] = $bl_achat;
        }

        $this->rapport->titres(
            array(
                champ_libre('bl_vente_lignes','numero_de_serie')->modele->nom,
                traduction('rapport.mouvement_numero_de_serie.colonnes.type_numero_serie'),
                traduction('rapport.mouvement_numero_de_serie.colonnes.quantite'),
                ucfirst(table_libre('article')->element),
                traduction('rapport.mouvement_numero_de_serie.colonnes.document')
            )
        );

        foreach ($bls_par_fournisseur as $fournisseur_id => $bls) {

            $fournisseur = management('fournisseur', $fournisseur_id);

            $this->rapport->sous_titre($fournisseur->affiche());


            foreach($bls as $bl) {
                $article = management('article', $bl['article_id']);

                $ligne = array(

                    $bl['numero_de_serie'],
                    $article->champ('type_numero_de_serie')->affiche(),
                    $bl['quantite'],
                    $article->affiche(),
                    management($bl['type_element'], $bl['document_id'])->affiche(),

                );

                $this->rapport->ligne($ligne);
            }
        }


        return $this->rapport->genere($ajax);
    }


}