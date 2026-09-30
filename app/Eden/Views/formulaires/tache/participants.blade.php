<div class="row">
    <div class="col-sm-2">
        @traduction('formulaires.tache.participants.organisateur')
    </div>
    <div class="col-sm-4" v-if="!tache.participant">
        {!! management('tache')->champ('affectation')->attr(':lecture_seule', 'tache.id && participants.length > 0')->cree() !!}
    </div>
    <div class="col-sm-4" v-else-if="organisateur">
        <span v-text="organisateur?.affichage_pour_recherche"></span>
    </div>
    <div class="col-sm-4" v-else-if="tache.adresse_email_organisateur">
        <span v-text="tache.adresse_email_organisateur"></span>
    </div>
</div>
<div class="row" v-if="tache.participant">
    <div class="col-sm-2">
        @traduction('champs_libres.tache.affectation.nom')
    </div>
    <div class="col-sm-4" >
        {!! management('tache')->champ('affectation')->attr(':lecture_seule', 'tache.id && participants.length > 0')->cree() !!}
    </div>
</div>

<div class="row">
    <div class="col-sm-2">
        <span>@traduction('formulaire.tache.participants.titre')</span>
    </div>
    <div class="col-sm-10" style="display:flex;">
        <input type="hidden" name="participants" v-if="participants && participants.length > 0" :value="JSON.stringify(participants)"/>
        <input type="hidden" name="participants" v-else :value="null"/>
        <div class="champ_selection_element_multiple">
            <div class="champ_valeurs">
                <div class="champ" v-if="!tache.participant">
                    <input type="text" name="recherche_participants" id="recherche_participants" placeholder="Rechercher..." v-model="recherche_en_cours" @keyup="recherche_email_debounced()"/>
                    <div v-show="affichage_select && recherche_en_cours !== ''" id="tache_participants" class="css_select_multiselection_element">
                        <div class="affichage_valeur" @click="ajouter_participant(participant)" v-for="participant in participants_selectionnables">
                            <span v-html="affichage_participant(participant)"></span>
                        </div>
                        <div class="chargement" v-if="chargement_select">
                            <img src="{{asset('eden/images/ajax_loader.gif')}}" />
                        </div>
                        <div class="aucun_resultat" v-else-if="participants_selectionnables.length == 0">
                            @traduction('composant.champ_selection_element_multiple.aucun_resultat')
                        </div>
                    </div>

                </div>
                <template v-if="participants.length > 0">
                    <div class="block_selection_multiple_elements" v-for="(participant,index_element) in participants">
                        <div class="css_selection_element_multiple css_selection_participants">
                            <span class="css_selection_element_multiple_icone" @click="supprimer_participant(participant.element_id, participant.type_element)" v-if="!tache.participant">
                                <span class="fa fa-times"></span>
                            </span>
                            <span v-if="participant.statut_participant === 1">
                                <i class="fas fa-check icone_accepte"></i>
                            </span>
                            <span v-else-if="participant.statut_participant === 2">
                                <i class="fas fa-times icone_refuse"></i>
                            </span>
                            <span v-else-if="participant.statut_participant === 3">
                                <i class="fas fa-question icone_provisoire"></i>
                            </span>
                            <span v-else>
                                <i class="fas fa-slash icone_non_repondu"></i>
                            </span>
                            <span class="ml-5" v-html="affichage_participant(participant)"></span>
                        </div>
                    </div>

                </template>
            </div>
            <div class="phrase_statuts">
                <span v-text="phrase_statuts"></span>
            </div>
        </div>
    </div>
</div>
<template v-if="fonctionnalite_microsoft">
    <div class="row">
        <div class="col-sm-2">
            @traduction('champs_libres.tache.visioconference.nom')
        </div>
        <div class="col-sm-4">
            {!! 
                management('tache')
                ->champ('visioconference')
                ->attr('lecture_seule', 'tache.id && tache.visioconference', 1)
                ->cree() 
                !!}
        </div>
    </div>
</template>

@push('donnees_pour_vuejs_data')

    participants: [],
    organisateur: {},
    participants_initiaux: [],
    participants_selectionnables: [],
    client_id : null,
    recherche_en_cours : '',
    affichage_select : false,
    chargement_select : false,
    desactivation_visioconference : false,
    fonctionnalite_microsoft : {!! fonctionnalite('microsoft_utiliser_connexion') ? 'true' : 'false' !!},
@endpush

@push('donnees_pour_vuejs_computed')

    phrase_statuts(){

        if(!this.participants)
            return '';

        var nb_non_repondus = 0;
        var statuts = Object.groupBy(this.participants, (participant) => {

            return participant.statut_participant;
        })

        var phrase = "";

        if(statuts[1] != undefined && statuts[1].length > 0)
            phrase += this.$root.traduction('composant.tooltip_tache.acceptes', null, [statuts[1].length]);

        if(statuts[2] != undefined && statuts[2].length > 0)
            phrase += this.$root.traduction('composant.tooltip_tache.refuses', null, [statuts[2].length]);

        if(statuts[3] != undefined && statuts[3].length > 0)
            phrase += this.$root.traduction('composant.tooltip_tache.provisoires', null, [statuts[3].length]);

        if(statuts[0] != undefined && statuts[0].length > 0)
            nb_non_repondus += statuts[0].length;

        if(statuts[null] != undefined && statuts[null].length > 0)
            nb_non_repondus += statuts[null].length;

        if(nb_non_repondus > 0)
            phrase += this.$root.traduction('composant.tooltip_tache.non_repondus', null, [nb_non_repondus]);

        return phrase;
    },
@endpush
@push('donnees_pour_vuejs_methods')

    affichage_participant : function(participant) {

        let affichage_participant = participant.affichage_pour_recherche;

        if(participant.type_element != undefined)
            affichage_participant += '(' + this.$root.traduction('tables_libres.'+participant.type_element+'.element') + ')';
        else
            affichage_participant += ' (' + this.$root.traduction('interface.listes.recherche') + ')';

        return affichage_participant;
    },

    participants_modifies : function() {

        return JSON.stringify(this.participants) !== JSON.stringify(this.participants_initiaux);
    },

    ajouter_participant: function(participant) {

        this.recherche_en_cours = '';
        this.affichage_select = false;
        this.participants.push(participant);

        if(fonctionnalite_microsoft && !this.desactivation_visioconference && this.participants.length > 0)
            this.$set(this.tache,'visioconference', 1);
    },

    supprimer_participant: function(id_participant, type_element) {
    
        this.participants.forEach((participant, index) => {

            if(participant.element_id === id_participant && participant.type_element == type_element)
                this.participants.splice(index, 1);
        });

        if(fonctionnalite_microsoft && !this.tache.id && !this.desactivation_visioconference && this.participants.length === 0)
            this.$set(this.tache,'visioconference', 0);
    },

    recherche_email: function() {

        if(this.recherche_en_cours == '')
            return;

        if(!this.affichage_select)
            this.affichage_select = true;

        this.chargement_select = true;

        $.post({
            url: '/eden/tache/rechercher_participants_possibles',
            dataType: "json",
            data: {
                tache : this.tache,
                recherche: this.recherche_en_cours
            },
        }).done((donnees) => {
            
            this.participants_selectionnables = donnees;

            this.chargement_select = false;

            this.participants_selectionnables.unshift({
                adresse_email: this.recherche_en_cours,
                statut_participant: null,
                affichage_pour_recherche : this.recherche_en_cours,
            });
        });
    },

    recuperer_client_projet : async function(){

        if(!this.tache.projet_id)
            return;

        await $.ajax({
            url: "eden/element/projet/" + this.tache.projet_id,
            dataType: "json"
        }).done((element) => {

            if(element.client_id != undefined && element.client_id != "")
                this.client_id = element.client_id;
            else
                this.client_id = null;
        });
    },

    recuperer_participants : function() {

        $.post({
            url: "eden/tache/" + this.tache.id + "/recuperer_details",
            dataType: "json"
        }).done((donnees) => {

            this.participants = structuredClone(donnees.participants);
            this.participants_initiaux = structuredClone(donnees.participants);
            this.organisateur = structuredClone(donnees.organisateur);
        });
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    if((this.tache.id == false || this.tache.id == undefined))
        this.$set(this.tache, 'affectation', (this.tache.affectation ? this.tache.affectation : this.$root.moi.id));

    //aller chercher le client du projet en récupérant l'élément si un projet est défini
    if(this.tache.client_id != undefined && this.tache.client_id != "")
        this.client_id = this.tache.client_id;
    else if(this.tache.projet_id != undefined && this.tache.projet_id != "")
        this.recuperer_client_projet()

    this.recherche_email_debounced = _.debounce(this.recherche_email, 200);

    if(this.tache.id != null)
        this.recuperer_participants();

    this.$on('changement_valeur', (parametres) => {
        
        if(parametres.nom_sql !== 'visioconference')
            return;

        if(fonctionnalite_microsoft && this.participants.length > 0 && !parametres.valeur)
            this.desactivation_visioconference = 1;
    });
@endpush

@push('donnees_pour_vuejs_watch')

    'tache.projet_id': function(nouvelle_valeur, ancienne_valeur) {

        if(nouvelle_valeur != null && nouvelle_valeur != "" && nouvelle_valeur > 0)
            this.recuperer_client_projet();
        else if(!this.tache.client_id)
            this.client_id = null;
    },

    'tache.client_id': function(nouvelle_valeur, ancienne_valeur) {

        if(nouvelle_valeur && nouvelle_valeur > 0)
            this.client_id = nouvelle_valeur;
        else if(!this.tache.projet_id)
            this.recuperer_client_projet();
    },
@endpush