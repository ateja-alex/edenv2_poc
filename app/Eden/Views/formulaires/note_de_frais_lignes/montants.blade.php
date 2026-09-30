<div class="row" v-if="utilisation_devise_etrangere()">
    <div class="col-sm-4">
        @traduction('interface.intranet.ht_devise')
    </div>
    <div class="col-sm-8">
        <champ-montant
                :modele="note_de_frais_lignes"
                nom_sql="montant_devise"
                :valeur_non_vide="true"
                style_input="width:100%;"
                :lecture_seule="note_de_frais != undefined && note_de_frais.accepte == 1">
        </champ-montant>
    </div>
</div>
<div class="row">
    <div class="col-sm-4">
        {!!management('note_de_frais_lignes')->champ('montant_ht')->nom()!!}
    </div>
    <div class="col-sm-8">
        {!!management('note_de_frais_lignes')->champ('montant_ht')->attr('disabled', 'note_de_frais != undefined && note_de_frais.accepte == 1 || utilisation_devise_etrangere(false,\'montant_ht\')',true)->cree()!!}
        <input type="hidden"
                name="montant_ht"
                :value="note_de_frais_lignes.montant_ht">
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        {!!management('note_de_frais_lignes')->champ('taux_tva')->nom()!!}
    </div>
    <div class="col-sm-8">
        {!!management('note_de_frais_lignes')->champ('taux_tva')->attr('disabled', 'note_de_frais != undefined && note_de_frais.accepte == 1',true)->cree()!!}
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        {!!management('note_de_frais_lignes')->champ('montant_ttc')->nom()!!}
    </div>
    <div class="col-sm-8">
        {!!management('note_de_frais_lignes')->champ('montant_ttc')->attr('disabled', 'note_de_frais != undefined && note_de_frais.accepte == 1 || utilisation_devise_etrangere(false,\'montant_ttc\')',true)->cree()!!}
        <input type="hidden"
                name="montant_ttc"
                :value="note_de_frais_lignes.montant_ttc">
    </div>
</div>

@push('donnees_pour_vuejs_data')
    articles_pour_note_de_frais: {},
    taux_de_tva: {},
    devises : [],
    note_de_frais : {},
@endpush

@push('donnees_pour_vuejs_created')

    this.recuperation_informations_note_de_frais();

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

    this.$on('maj_champ_montant',(donnees) => {

        if(donnees.nom_sql == 'montant_ht')
            this.mise_a_jour_montant_ligne_ht();
        else if(donnees.nom_sql == 'montant_ttc')
            this.mise_a_jour_montant_ligne_ttc();
        else if(donnees.nom_sql == 'montant_devise'){
            this.note_de_frais_lignes.montant_ht = Math.round(this.note_de_frais_lignes.montant_devise * this.note_de_frais.taux_de_change *100,2)/100;
            this.mise_a_jour_montant_ligne_ht(false);
        }
    });

    this.$root.$on('selection-element',(parametres) => {

        if(parametres.nom_champ == 'note_de_frais_id'){
            this.note_de_frais = parametres.element;
            this.$set(this.note_de_frais_lignes,'montant_devise',Math.round(this.note_de_frais_lignes.montant_ht / this.note_de_frais.taux_de_change * 100,2)/100);
        }
    });
@endpush

@push('donnees_pour_vuejs_methods')

    mise_a_jour_montant_ligne_ht : function(calcul_devise = true){

        var montant_ht = this.note_de_frais_lignes.montant_ht;
        var tva = this.note_de_frais_lignes.taux_tva;

        if(this.taux_de_tva[this.note_de_frais_lignes.taux_tva] == undefined)
            return;

        if(tva <= 0 || tva == undefined)
            tva = 0;
        else
            tva = this.taux_de_tva[this.note_de_frais_lignes.taux_tva].taux

        if(!isNaN(montant_ht) && montant_ht > 0){

            montant_ttc = montant_ht * (1 + tva/100);
            this.note_de_frais_lignes.montant_ttc = Math.round(montant_ttc * 100,2)/100;

            if(this.utilisation_devise_etrangere() && calcul_devise)
                this.note_de_frais_lignes.montant_devise = Math.round(this.note_de_frais_lignes.montant_ht / this.note_de_frais.taux_de_change * 100,2)/100;
        }
    },

    mise_a_jour_montant_ligne_ttc : function(){

        var montant_ttc = this.note_de_frais_lignes.montant_ttc;
        var tva = this.note_de_frais_lignes.taux_tva;

        if(this.taux_de_tva[this.note_de_frais_lignes.taux_tva] == undefined)
            return;

        if(tva <= 0 || tva == undefined)
            tva = 0;
        else
            tva = this.taux_de_tva[this.note_de_frais_lignes.taux_tva].taux

        if(!isNaN(montant_ttc) && montant_ttc > 0){
            montant_ht = montant_ttc / (1 + tva/100);
            this.note_de_frais_lignes.montant_ht = Math.round(montant_ht * 100,2)/100;

            if(this.utilisation_devise_etrangere())
                this.note_de_frais_lignes.montant_devise = Math.round(this.note_de_frais_lignes.montant_ht / this.note_de_frais.taux_de_change * 100,2)/100;
        }
    },

    recuperation_informations_note_de_frais(){

        $.get({

            url: "{{URL::to('eden/note_de_frais/informations')}}",
            dataType: "json",
            method: 'GET'
        }).done((donnees) => {

            this.articles_pour_note_de_frais = donnees.articles_pour_note_de_frais;
            this.taux_de_tva = donnees.taux_de_tva;
        });
    },

    utilisation_devise_etrangere(uniquement_fonctionnalite = false,champ = false){

		var fonctionnalite = "{{fonctionnalite('saisie_documents_devise_etrangere')}}" == 1 ? true : false;

		if(uniquement_fonctionnalite)
			return fonctionnalite === true;

		var griser_prix = "{{fonctionnalite('documents_devise_etrangere_griser_prix_converti')}}" == 1 ? true : false;

        if(champ !== false && griser_prix === false)
            return false;

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

    'note_de_frais_lignes.taux_tva' : function(){

        this.mise_a_jour_montant_ligne_ht();
    },
@endpush
