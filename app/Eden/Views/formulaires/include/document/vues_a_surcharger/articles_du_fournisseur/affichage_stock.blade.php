@{{ article.stock_actuel }}
<span  v-show="article.stock_en_jours.length > 0">
	<span :title="traduction('interface.document.affichage_stock.sorties_stock')">(@{{ article.stock_en_jours }})</span>
</span>
(@traduction('document.vues_a_surcharger.affichage_stock.dont') @{{ article.stock_reserve }} @traduction('document.vues_a_surcharger.affichage_stock.en_attente'))