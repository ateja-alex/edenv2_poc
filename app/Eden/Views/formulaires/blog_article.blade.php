<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.divers.informations_generales')</div>
</div>
<div class="row">
    @champ('blog_article', 'titre', 2,6)
    @champ('blog_article', 'date', 2,2)
</div>
<div class="row">
    @champ('blog_article', 'url', 2,10)
</div>
<div class="row">
    @champ('blog_article', 'categorie_id', 2,10)
</div>
<div class="row">
    @champ('blog_article', 'contenu', 12,12)
</div>
<div class="row">
    @champ('blog_article', 'image_principale', 3,9)
</div>
<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.article.informations_seo')</div>
</div>
<div class="row">
    @champ('blog_article', 'titre_seo', 2,4)
    @champ('blog_article', 'mots_cles_seo', 2,4)
</div>
<div class="row">
    @champ('blog_article', 'description_seo', 12,12)
</div>



