<div class="row" v-if="utilisation_devise_etrangere(true)">
    @champ('note_de_frais','devise',4,3)
    <template v-if="utilisation_devise_etrangere()">
        @champ('note_de_frais','taux_de_change',3,2)
    </template>
</div>
@push('donnees_pour_vuejs_data')
    devises : [],
@endpush

@include('eden::formulaires.note_de_frais.include.maj_montant')

@push('donnees_pour_vuejs_mounted')
    if(this.utilisation_devise_etrangere(true))
        $.post({
            url : 'eden/elements/devise',
            dataType:'json',
            data:{
                filtrage:[
                    {
                        champ : 'disponible',
                        condition : 'where',
                        valeur : 1
                    },
                ]
            }
        }).done((elements) => {
            this.devises = elements;
        });

    if(this.$root.intranet){
        this.$on('maj_champ_montant',(donnees) => {

            if(donnees.nom_sql == 'taux_de_change'){

                for(article of this.note_de_frais.articles){

                    article.montant_ht = article.montant_devise * this.note_de_frais.taux_de_change;
                    this.$root.mise_a_jour_montant_ligne_ht(article, false);
                }
            }
        });
    }
@endpush

@push('donnees_pour_vuejs_methods')
    utilisation_devise_etrangere(uniquement_fonctionnalite = false){

		var fonctionnalite = "{{fonctionnalite('saisie_documents_devise_etrangere')}}" == 1 ? true : false;

		if(uniquement_fonctionnalite)
			return fonctionnalite === true;

        if(this.devise_euro_id === undefined)
            return false;

		if(this.note_de_frais.devise == 0 || this.note_de_frais.devise == null || this.note_de_frais.devise == undefined)
			this.note_de_frais.devise = this.devise_euro_id;

        return fonctionnalite === true && parseInt(this.note_de_frais.devise) !== parseInt(this.devise_euro_id);
    },

@endpush

@push('donnees_pour_vuejs_computed')
    devise_euro_id(){
        var devise_euro_id = 0;

        $.each(this.devises,(index,devise) => {

            if(devise.code == '{!! maquette('devise_application_iso') !!}'){
                devise_euro_id = devise.id;
                return;
            }
        });

        return devise_euro_id.toString();
    },
    code_devise(){

        var code_devise = '{!! maquette('devise_application_iso') !!}';

        $.each(this.devises,(index,devise) => {

            if(this.note_de_frais.devise == devise.id){
                code_devise = devise.code;
                return;
            }
        });

        if(code_devise != '')
            return code_devise;

        return '{!! maquette('devise_application_iso') !!}';
    },
@endpush

@push('donnees_pour_vuejs_watch')
    'note_de_frais.taux_de_change' : {
        handler: function() {
            var vue_instance = this;

            if(isNaN(this.note_de_frais.taux_de_change) || this.note_de_frais.taux_de_change <= 0)
                return false;

            var champ_montant = 'montant_ht';

            var champ_montant_devise = 'montant_ht_devise';

            for(article of this.note_de_frais.articles){
                article.montant_ht = article.montant_devise * this.note_de_frais.taux_de_change;

                if(this.$root.intranet){
                    this.$root.mise_a_jour_montant_ligne_ht(article, false);
                } else {

                    var montant_ht = article.montant_ht;
                    var tva = article.taux_tva;

                    if(tva <= 0 || tva == undefined)
                        tva = 0;
                    else
                        tva = this.taux_de_tva[article.taux_tva].taux

                    var montant_ttc = 0;

                    if(!isNaN(montant_ht))
                        montant_ttc = montant_ht * (1 + tva/100);

                    article.montant_ttc = Math.round(montant_ttc * 100,2)/100;

                    this.mise_a_jour_montant();
                }
            }
        },
        deep:true,
    },
@endpush