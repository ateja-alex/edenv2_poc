<!-- Blacklister / Déblacklister -->
<i class="css_action_icon primaire fa fa-fw fa-ban" v-if="ticket_client.blackliste == 0 || ticket_client.blackliste == null" @click="blacklister(1)" title="{{traduction('module_sur_fiche.ticket_client.blacklister')}}"></i>
<i class="css_action_icon primaire fa fa-fw fa-check" v-if="ticket_client.blackliste == 1" @click="blacklister(0)" title="{{traduction('module_sur_fiche.ticket_client.deblacklister')}}"></i>

<!-- Ajouter l'email à la blacklist -->
<i class="css_action_icon primaire fa fa-fw fa-user-slash" @click="ajout_email_blacklist()" title="{{traduction('module_sur_fiche.ticket_client.ajout_email_blacklist')}}" data-toggle="tooltip"></i>

@push('donnees_pour_vuejs_methods')

    blacklister: function(valeur){

        $.post({

            url: "{{ URL::to('eden/element/ticket_client') }}/"+this.ticket_client.id+"/enregistrer",
            dataType: "json",
            method: "post",
            data: {

                blackliste: valeur,
            }
        }).done(async (donnees) => {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            this.ticket_client = donnees.element;

            if(valeur)
                toastr.success(this.$root.traduction('messages.js.ticket_client.blacklister.enregistrement_reussi'));
            else
                toastr.success(this.$root.traduction('messages.js.ticket_client.deblacklister.enregistrement_reussi'));
        });
    },

    ajout_email_blacklist: function(){

        $.post({

            url: "/eden/element/ticket_client_blacklist_emails/creer",
            dataType: "json",
            method: "post",
            data: {

                adresse_email: this.ticket_client.from_email,
                type_blocage: 1,
            }
        }).done(async (donnees) => {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            toastr.success(this.$root.traduction('messages.js.ticket_client.ajout_email_blacklist'));
        });
    },
@endpush