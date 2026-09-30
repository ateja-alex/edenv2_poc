{{-- la surveillance porte sur le conteneur : il englobe le bouton, qui ne doit pas compter comme un clic exterieur --}}
<div v-clique_en_dehors="{ func: ajout_ligne_clique_en_dehors, params: [article_sur_document] }" class="ajout_ligne_par_ligne" style="position:relative">
    <div  style="cursor:pointer; margin-bottom: 0px !important; margin-right: 2px;position:relative" :title="traduction('interface.document.tableau_des_articles.ajouter_ligne')" class="mb-1 css_btn_action_article_document" @click="affichage_options_ajout_ligne = affichage_options_ajout_ligne == article_sur_document.index_article ? null : article_sur_document.index_article">
        <i class="fa fa-plus"></i>
        <i class="fa fa-arrow-down" style="position: absolute;font-size: 7px;padding: 3px;bottom: 2px;right: 3px;"></i>
    </div>
    <div v-if="affichage_options_ajout_ligne == article_sur_document.index_article" style="position: absolute;background: white;z-index: 1000;padding: 0 15px 15px;width: max-content;right: 0px;left: unset;top: 100%;transform: unset;box-shadow: rgba(0, 0, 0, 0.176) 0px 2px 15px;border: 1px solid rgba(0, 0, 0, 0.176);display: flex;flex-direction: column;gap: 10px;" @click="afficher_scroll_articles_autres.ajout_ligne = true">
        <div class="row css_row_ajouter_article_au_document" style="padding-top: 1%;">
            <div class="col-md-12" style="position:relative;height: 50px;">
                <input type="text" :placeholder="traduction('interface.document.articles.ajouter')" id="js_valeur_champ_recherche" v-model="valeur_champ_recherche_modale" @keyup="afficher_articles(true)"  style="background: #f9f9f9; color: #272727; padding: 14px 10px; height: 50px;border:2px solid var(--background_menus);position: absolute;top: 0;left: 0;">@if(fonctionnalite('gescom_garder_valeur_saisie_des_articles') === true)<span class="fas fa-times" @click="valeur_champ_recherche = ''" style="position: absolute;top: 50%;right: 20px;transform: translateY(-50%);cursor: pointer;font-size: 17px;"></span>@endif
            </div>
        </div>
        <div class="row" v-if="valeur_champ_recherche_modale != ''" style="margin-bottom: 2%;" >
            <div class="col-md-12" >
                @include('eden::formulaires.include.document_affichage_resultat_recherche',['type' => 'ajout_ligne'])
            </div>
        </div>
        @include('eden::formulaires.include.document.includes.saisie_des_articles_options',['ajout_ligne' => true])
    </div>
</div>

@push('donnees_pour_vuejs_data')
    affichage_options_ajout_ligne : null,
@endpush

@push('donnees_pour_vuejs_methods')

    ajout_ligne_clique_en_dehors : function(article_sur_document){

        // aucun panneau ouvert : rien a fermer.
        // sans ce garde, null == undefined est vrai et chaque ligne sans index ferme la recherche en cours
        if(this.affichage_options_ajout_ligne === null || this.affichage_options_ajout_ligne === undefined)
            return;

        if(article_sur_document.index_article === undefined)
            return;

        if(this.affichage_options_ajout_ligne != article_sur_document.index_article)
            return;

        this.affichage_options_ajout_ligne = null;
        this.afficher_scroll_articles_autres.ajout_ligne = false;
    },
@endpush


