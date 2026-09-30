<template v-if="(articles.filter(article => article.id == ligne.element.article_id)[0] ?? []).type_article == 1">
    @include('eden::listes.includes.options.zoom')
</template>

@push('donnees_pour_vuejs_data')
    articles : [],
@endpush

@push('donnees_pour_vuejs_mounted')
    this.$parent.$on('actualisation_liste',() => {

        var articles_a_recuperer = this.liste.lignes
            .map(ligne => ligne.element.article_id)
            .filter((article,index, articles) =>
                articles.indexOf(article) === index && this.articles.filter(article_actuel => article_actuel.id == article).length == 0);

        if(articles_a_recuperer.length > 0){
            $.post({
                url : 'eden/elements/article',
                dataType:'json',
                data:{
                    filtrage:[
                        {
                            champ : 'id',
                            condition : 'whereIn',
                            valeur : articles_a_recuperer
                        },
                    ]
                }
            }).done((elements) => {
                this.$set(this,'articles',this.articles.concat(elements));
            });
        }
    });
@endpush