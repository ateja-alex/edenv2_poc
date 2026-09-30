<h3 class='css_titre_categorie_recap_devis' v-show="panier_classique.articles_dans_panier && panier_classique.articles_dans_panier.length > 0">Accessoires</h3>
<table class="css_table_recap_devis css_table_tablier_recap_devis js_basictable" v-show="panier_classique.articles_dans_panier && panier_classique.articles_dans_panier.length > 0">
    <tbody v-for="(article, index)  in panier_classique.articles_dans_panier">
        <tr class="v-align-top hidden-xs hidden-sm">
            <td>
                <span class="text-bold">
                    Aperçu
                </span>
            </td>
			<td>
                <span class="text-bold">
                    Désignation
                </span>
            </td>
            <td>
                <span class="css_big_green_text css_table_width_prix_aligner">
                    PU TTC
                </span>
            </td>
            <td>
                <span class="css_big_green_text css_table_width_prix_aligner">
                    QT
                </span>
            </td>
            <td>
                <span class="css_big_green_text css_table_width_prix_aligner">
                    Tarif
                </span>
            </td>
            <td>
                <span class="css_big_green_text css_table_width_prix_aligner">
                    X
                </span>
            </td>
        </tr>
        <tr>
            <td class="css_td_apercu_recap_devis" rowspan="1">
                <div class="css_bg_apercu_recap_devis">
                    <img :src="article.article.image_principale.replace('uploads/', '')" alt="" v-show="article.article.image_principale_declinaison == null || article.article.image_principale_declinaison == ''">
                    <img :src="'{{asset('storage')}}/'+article.article.image_principale_declinaison" alt="" v-show="article.article.image_principale_declinaison != null && article.article.image_principale_declinaison != ''">
                </div>
            </td>
            <td class="">
                @{{ article.article.designation }} 
            </td>
			<td class="css_table_width_prix_aligner text-bold">
                @{{ article.article.tarif.toFixed(2) }} &euro;
            </td>
			<td class="css_table_width_prix_aligner text-bold qte">
                @{{ article.quantite }}
            </td>
			<td class="css_table_width_prix_aligner text-bold">
                @{{ (article.article.tarif * article.quantite).toFixed(2) }} &euro;
            </td>
			<td class="css_table_width_prix_aligner" @click="supprime_accessoire(index, article.article.id)">
                <img src="{{ asset('ecommerce-amc/images/pictos/poubelle.png') }}" alt="">
            </td>
        </tr>
    </tbody>
</table>
