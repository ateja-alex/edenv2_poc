<div class="cellule_document_colonne_article w-100">

    <!-- Si il y a plusieurs codes articles -->
    <select v-model="article_sur_document.code_article" id="" @if(!empty($recapitulatif)) disabled="disabled" @endif v-if="{{ $edition_ligne }} && article_sur_document.choix_code_article != null && Object.keys(article_sur_document.choix_code_article).length > 1">
        <option :value="code_article" v-for="(nom_reference_article, code_article, index) in article_sur_document.choix_code_article">@{{nom_reference_article}}</option>
    </select>

    <!-- Un seul choix, ou ligne non editee : on affiche la valeur retenue -->
    <div v-else-if="article_sur_document.choix_code_article != null">
        <div>
                @{{ article_sur_document.choix_code_article[article_sur_document.code_article] != undefined ? article_sur_document.choix_code_article[article_sur_document.code_article] : Object.values(article_sur_document.choix_code_article)[0] }}
            <input type="hidden" v-model="article_sur_document.code_article" />
        </div>
    </div>

    <input type="hidden" v-model="article_sur_document.article_id" />
</div>
