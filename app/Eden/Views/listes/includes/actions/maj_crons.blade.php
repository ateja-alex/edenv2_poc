@push('donnees_pour_vuejs_methods')

    /**
    *
    * On récupère les crons pour les enregistrer en base, puis on recharge la liste
    *
    */
    maj_crons: function() {

        loading(true);
        var vue_instance = this;

        $.ajax({

            method: 'GET',
            dataType: 'json',
            url: '/eden/maintenance/maj_crons'
        }).done(function(donnees) {

            loading(false);
            vue_instance.actualisation_filtres();
        });
    },
@endpush
