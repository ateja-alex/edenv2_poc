<div class="row">
    @champ('compte_email', 'aliases', 4, 8)
</div>

<div class="row" v-if="compte_email.type_de_compte == 1">
    <div class="col-sm-4">
        Alias déjà présents sur le compte Microsoft
    </div>
    <div class="col-sm-8">
        <ul>
            <li v-for="alias in aliases" v-html="alias"></li>
        <ul>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    aliases : [],
@endpush

@push('donnees_pour_vuejs_mounted')
    this.chargement_aliases_microsoft();
@endpush

@push('donnees_pour_vuejs_methods')

    chargement_aliases_microsoft : function(){

        if(this.compte_email.type_de_compte == 1 && this.compte_email.utilisateur_id > 0){

            $.ajax({
                url: 'eden/microsoft/alias_email/'+this.compte_email.utilisateur_id,
                dataType:'json',
            }).done((alias) => {
                this.aliases = alias;
            });
        }
    },
@endpush

@push('donnees_pour_vuejs_watch')

    'compte_email.utilisateur_id' : function(){
        this.chargement_aliases_microsoft();
    },

    'compte_email.type_de_compte' : function(){
        this.chargement_aliases_microsoft();
    },

@endpush