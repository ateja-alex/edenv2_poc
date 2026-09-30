<div id="scroll_articles"  v-if="@if(isset($type))afficher_scroll_articles_autres.{{$type}}@else afficher_scroll_articles @endif" @scroll="scroll_article($event)" >
    <div class="row" style="text-align: center;border: 1px solid rgba(0,0,0,.125); margin:0;padding: 0; border-width: 0px 0px 1px 0px;">
        <div class="col-md-2" style="text-align: left;"><b style="font-size: 16px">@traduction('document.affichage_resultat_recherche.famille')</b></div>
        <div class="col-md-8" style="text-align: left;"><b style="font-size: 16px">@traduction('document.affichage_resultat_recherche.designation')</b></div>
        <div class="col-md-1" style="text-align: right;"><b style="font-size: 16px">@traduction('document.affichage_resultat_recherche.tarif')</b></div>
        @if($management->est_une_vente())
        <div class="col-md-1" style="text-align: right;"><b style="font-size: 16px">@traduction('document.affichage_resultat_recherche.prix_achat')</b></div>
        @endif
    </div>
    <div id="chargement_articles" v-if="chargement_select">
        <img style="width: 35px;height: 35px;" src="/eden/images/ajax_loader.gif" />
    </div>
    <div class="row js_resultat_recherche css_resultat_recherche" v-for="article in articles_match" @if(isset($type)) @if(in_array($type,['modale','ajout_ligne'])) @click="ajouter_article_via_modale(article)" @else @click="remplacer_article_via_modale(article)" @endif @else @click="ajouter_article(article)" @endif style="border: 1px solid rgba(0,0,0,.125);margin: 0;padding: 3px;border-width: 0px 0px 1px 0px; line-height: 18px; cursor: pointer;">
        <div class="col-md-2" style="margin: 0;padding: 0;display: flex;align-items: center;">
            @{{article.famille_id | affiche_famille}}
        </div>
        <div class="col-md-8" style="margin: 0;padding: 0;">
            <div class="row">
                <div v-if="article.image" class="col-md-1">
                    <img :src="'../storage/app/public/'+article.image" style="width: 75px" v-if="article.image != null">
                </div>
                <div class="col-md-11">
                    <b v-html="article.code_article"></b>
                    @section('badge_designation_article')
                        <span class="badge badge-warning" style="background-color:#c37710;" v-show="article.type_article == 1">{{ management('article')->champ('type_article')->affiche(1) }}</span>
                        <span class="badge badge-warning" style="background-color:#1c69b1;" v-show="article.type_article == 3">{{ management('article')->champ('type_article')->affiche(3) }}</span>
                        <span class="badge badge-danger" v-show="article.type_article == 2">@traduction('document.affichage_resultat_recherche.frais_de_port')</span>
                        <span class="badge badge-warning" v-show="article.pack == 1">@traduction('document.affichage_resultat_recherche.pack_articles')</span><br>
                    @endsection
                    @yield('badge_designation_article')
                </div>
            </div>
        </div>
        <div class="col-md-1" style="margin: 0;padding: 0; text-align: center;display: flex;align-items: center; justify-content: flex-end;">
            <span v-if="article.tarif != null">@{{article.tarif | montant}}</span>
        </div>
        @if($management->est_une_vente())
            <div class="col-md-1" style="margin: 0px;padding: 0px;text-align: center;display: flex;justify-content: center;flex-direction: column;align-items: flex-end;">
                <span v-if="article.prix_d_achat != null">@{{article.prix_d_achat | montant}}</span>
                @if(fonctionnalite('gescom_affichage_prix_achat_liste_articles'))
                    <span v-if="article.date_prix_achat != null">(@{{article.date_prix_achat | date }})</span>
                @endif
            </div>
        @endif
    </div>
</div>
