<div class="row">
    <div class="col-sm-2">
        {!!management('conditionnement')->champ('quantite')->nom()!!}
    </div>

    <div class="col-sm-4">
        <template v-if="indicateur_sans_quantite != undefined && indicateur_sans_quantite != ''">
            <div style="display:inline-flex;width:100%">
                {!!management('conditionnement')->champ('quantite')->cree()!!}
                <div style="padding:0 10px 0 10px;background:lightgrey">@{{indicateur_sans_quantite}}</div>
            </div>
        </template>
        <template v-else>
            {!!management('conditionnement')->champ('quantite')->cree()!!}
        </template>
    </div>
</div>

@push('donnees_pour_vuejs_computed')

    indicateur_sans_quantite : function(){

        var component = this;

        var valeur = "";

        var article_unite = 0;

        if(component.article != undefined)
            article_unite = component.article.unite;

        else if( component.$root.article !=undefined)
            article_unite = component.$root.article.unite;

        if(article_unite > 0){
            $.each(component.$root.valeurs_listes_formatees[81],function(index,liste){
                if(liste.id_valeur == article_unite){
                    valeur =  liste.valeur;

                    return true;
                }
            });
        }

        return valeur;
    },
@endpush