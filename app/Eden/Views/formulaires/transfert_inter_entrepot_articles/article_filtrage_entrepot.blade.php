@php
    $management_champ = management('transfert_inter_entrepot_articles')->champ('article_id');
@endphp
<div class="row">
    <div class="col-sm-2">
        {!! $management_champ->nom_vue() !!}
    </div>
    <div class="col-sm-4" style="position:relative">
        <span style="position:relative">
            <span v-if="chargement_entrepot" style="position: absolute;width: 100%;z-index: 8;display: flex;justify-content: center;background: #5353535c;">
                <img src="/eden/images/ajax_loader.gif" style="width:30px;" />
            </span>
            @php
                $management_champ->modele = clone $management_champ->modele;
                $management_champ->filtrage('[{\"champ\":\"id\",\"condition_ou\":false,\"condition\":\"WhereIn\",\"symbole\":\"\",\"valeur\":articles_entrepot.join(",")}]');
            @endphp
            {!! $management_champ->cree() !!}
        </span>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    articles_entrepot : [],
    chargement_entrepot: false,
@endpush

@push('donnees_pour_vuejs_mounted')
    if(this.sous_formulaire){
        this.$parent.$watch('transfert_inter_entrepot.entrepot_depart_id',(nouvelle_valeur) => {
            this.chargement_articles_entrepot(nouvelle_valeur);
        });
        this.chargement_articles_entrepot(this.transfert_inter_entrepot.entrepot_depart_id);
    }
    else{
        this.$root.$on('selection-element', (parametres) => {
            if(parametres.nom_champ == 'transfert_inter_entrepot_id')
                this.chargement_articles_entrepot(parametres.element.entrepot_depart_id);
        })
    }
@endpush

@push('donnees_pour_vuejs_methods')
    chargement_articles_entrepot : function(entrepot_depart_id){

        this.chargement_entrepot = true;

        $.post({
            url : 'eden/elements/stocks',
            dataType:'json',
            data:{
                filtrage:[
                    {
                        champ : 'entrepot_id',
                        condition : 'where',
                        valeur : entrepot_depart_id
                    },
                    {
                        champ : 'stock_actuel',
                        condition : 'where',
                        symbole : '>',
                        valeur : '0'
                    }
                ]
            }
        }).done((elements) => {
            this.articles_entrepot = elements.map(element => element.article_id);

            if(this.transfert_inter_entrepot_articles.article_id != null && !this.articles_entrepot.includes(this.transfert_inter_entrepot_articles.article_id))
                this.$set(this.transfert_inter_entrepot_articles,'article_id',null);

            this.chargement_entrepot = false;
        });
    },
@endpush