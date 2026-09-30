@include('eden::formulaires.tache.formulaire_rapide')

@push('donnees_pour_vuejs_watch')

    'tache.date_de_debut' : {
        handler: function (nouvelle_date_de_debut,ancienne_date_de_debut) {

            var tache = this.tache;

            if(tache.date_de_fin == null || ancienne_date_de_debut == undefined)
                return false;

            var moment_nouvelle_date_de_debut = moment(nouvelle_date_de_debut,'YYYY-MM-DD HH:mm:ss');
            var moment_ancienne_date_de_debut = moment(ancienne_date_de_debut,'YYYY-MM-DD HH:mm:ss');
            var moment_ancienne_date_de_fin = moment(tache.date_de_fin,'YYYY-MM-DD HH:mm:ss');

            if(moment_ancienne_date_de_debut < moment_ancienne_date_de_fin){

                var difference = moment_ancienne_date_de_fin - moment_ancienne_date_de_debut;

                var nouvelle_date_de_fin = moment_nouvelle_date_de_debut + difference;

                tache.date_de_fin = moment(nouvelle_date_de_fin).format('YYYY-MM-DD HH:mm:ss');

            }

        },
        immediate: true,
    },

@endpush

@push('donnees_pour_vuejs_computed')

    type_taches_a_gerer : function(){

        var instance = this;

        if(instance.$parent !== undefined && instance.$parent.$attrs.rendez_vous)
            return 'rdv';

        if(instance.tache != undefined && instance.tache.id != undefined && instance.tache.type_tache_rdv > 0 && instance.tache.type_tache_rdv > 0 && instance.tache.type_tache_rdv != undefined)
            return 'rdv';

        return null;
    },
@endpush