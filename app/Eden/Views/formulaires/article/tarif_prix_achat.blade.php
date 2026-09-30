@if(fonctionnalite('calcul_tarif_sur_fiche_article_avec_marge') !== true)
    <div class="row">
        @champ('article', 'tarif', 2,4)
        @champ('article', 'prix_d_achat', 2,4)
    </div>
@else
    <input type="hidden" name="prix_d_achat" v-model="article.prix_d_achat" />
    <input type="hidden" name="tarif" v-model="article.tarif" />
    <input type="hidden" name="marge_devises" v-model="article.marge_devises" />
    <input type="hidden" name="marge_pourcent" v-model="article.marge_pourcent" />
@endif