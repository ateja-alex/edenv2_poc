<div class="row">
    @champ('tache','projet_id',2,6)
</div>

@push('donnees_pour_vuejs_data')
    titre_sur_mesure : false,
@endpush

@push('donnees_pour_vuejs_mounted')

    var vue_instance = this;

    $("input[name='titre']").on('change',function(){
        vue_instance.titre_sur_mesure = true;
    });
@endpush

@push('donnees_pour_vuejs_methods')

    gere_retour_chargement_projet: function(projet) {

        if(this.tache.id == undefined && this.titre_sur_mesure === false)
            this.tache.titre = projet.nom;

        this.tache.client_id = projet.client_id;
    },

@endpush

@push('donnees_pour_vuejs_watch')

    'tache.projet_id': {
        handler: function(nouvelle_valeur, ancienne_valeur) {

            var vue_instance = this;

            if(ancienne_valeur != nouvelle_valeur && nouvelle_valeur != '' && nouvelle_valeur != undefined && nouvelle_valeur !== null) {

                $.ajax({
                    url: "{{ URL::to('/eden/element/projet') }}/" + vue_instance.tache.projet_id,
                    dataType: "json"
                }).done(function(donnee) {

                    vue_instance.gere_retour_chargement_projet(donnee);
                });
            }
        },
        deep: true,
    },

@endpush