<div class="row" v-if="integration_email_comptes_emails.valeur > 0 && Object.values(boites_mails).length > 0">
    <div class="col-sm-2">
        {!! management('integration_email_comptes_emails')->champ('boite_mail')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <select v-model="integration_email_comptes_emails.boite_mail" name="boite_mail">
            <option v-for="(boite_mail,index_boite) in boites_mails" :value="index_boite" v-html="boite_mail"></option>
        </select>
    </div>
    <div class="col-sm-2">
        {!! management('integration_email_comptes_emails')->champ('boite_deplacement_mail')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <select v-model="integration_email_comptes_emails.boite_deplacement_mail" name="boite_deplacement_mail">
            <option :value=null>Pas de déplacement</option>
            <option v-for="(boite_mail,index_boite) in boites_mails" :value="index_boite" v-html="boite_mail"></option>
        </select>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    boites_mails : {},
    utilisateurs : {!! collect(modele('utilisateur')->get()) !!},
@endpush

@push('donnees_pour_vuejs_mounted')
    this.$root.$on('selection-element',(parametres) => {
        this.chargement_boite_emails(parametres);
    });
@endpush

@push('donnees_pour_vuejs_methods')

    chargement_boite_emails : async function(parametres){

        if(!(this.integration_email_comptes_emails.valeur > 0))
            return;

        if(parametres.element.type_de_compte == 1){

            var utilisateur = this.utilisateurs.filter(utilisateur => utilisateur.id == parametres.element.utilisateur_id)[0] ?? null;

            if(utilisateur == null){
                this.boites_mails = {};
                return;
            }

            this.boites_mails = await $.ajax({

                url : '{{URL::to('/eden/microsoft/dossiers_boite_mail/')}}/'+utilisateur.email,
                dataType : 'json',
            }).catch(() => {
                return {};
            });
        }
        else
            this.boites_mails = {};
    },
@endpush