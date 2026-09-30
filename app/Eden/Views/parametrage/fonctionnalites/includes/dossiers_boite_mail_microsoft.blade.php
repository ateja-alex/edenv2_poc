@push('donnees_pour_vuejs_methods')

    recuperation_dossiers_boite_mail_microsoft : async function(fonctionnalite){

        var dossiers = [];

        if(this.parametres_fonctionnalites.releve_mail_ticket_client_microsoft_email){

            await $.ajax({

                url : '{{URL::to('/eden/microsoft/dossiers_boite_mail/')}}/'+this.parametres_fonctionnalites.releve_mail_ticket_client_microsoft_email,
                dataType : 'json',
            }).done(function(donnees){

                dossiers = donnees;
            });
        }

        for(fonctionnalites_par_categorie of Object.values(this.fonctionnalites)){

            for(fonctionnalite of fonctionnalites_par_categorie){

                if(fonctionnalite.fonctionnalite == 'releve_mail_ticket_client_microsoft_dossier')
                    fonctionnalite.valeurs_select = dossiers;

            }
        }
    },
@endpush