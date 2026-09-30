<div v-if="affichage_bloc('{{$type_saisie}}')" :class="!presence_bar_progression_apres_saisie ? 'mb-3' :''">
    @yield('saisie')
</div>

@push('donnees_pour_vuejs_data')

    elements:[],
    utilisateurs: [],
    loader_element: false,
    enregistrement_en_cours: null,
    parametrage_date : null,
    commentaires : [],
@endpush

@push('donnees_pour_vuejs_created')

    this.$on('chargement_element',(element_id) => {

        this.chargement_element(element_id);

    });

    this.$on('changement',() => {

        this.chargement_element();

    });

    this.$on('changement_type_element_dynamique',(donnees) => {
        this.feuille_de_temps.elements_ids = [];
        this.chargement_element();
    });

    this.feuille_de_temps.type_saisie = '{{$type_saisie}}';
@endpush

@push('donnees_pour_vuejs_computed')

    champ_de_duree : function(){

        return this.unite_de_saisie == 'jour' ? 'duree_jours' : 'duree';
    },

    unite_de_saisie : function(){

        var unite = '{{ $structure['options']['unite'] }}';

        if(unite == 'utilisateur') {
            if(this.utilisateur_actuel && this.utilisateur_actuel.type_contrat == 2)
                unite = 'jour';
            else
                unite = 'heure';
        }

        return unite;
    },

    utilisateur_actuel : function(){

        if(!this.feuille_de_temps || !this.feuille_de_temps.utilisateur_id)
            return {};

        var utilisateur_actuel = null;

        for(utilisateur of this.utilisateurs){

            if(utilisateur.id == this.feuille_de_temps.utilisateur_id)
                utilisateur_actuel = utilisateur;
        }

        return utilisateur_actuel;
    },

    presence_bar_progression_apres_saisie : function(){

        var presence_bar_progression_apres_saisie = false;

        var index_saisie = parseInt(Object.keys(this.structure_fiche.modules).filter(cle => ['saisie_element','saisie_categorie_ligne'].includes(this.structure_fiche.modules[cle].module))[0] ?? 0);

        var index_bar_progession = parseInt(Object.keys(this.structure_fiche.modules).filter(cle => this.structure_fiche.modules[cle].module == 'bar_progression')[0] ?? 0);

        if(index_bar_progession == (index_saisie + 1))
            return true;

        return false;
    },
@endpush

@push('donnees_pour_vuejs_methods')

    chargement_element : function(element_id = null){

        this.loader_element = true;

        var data = {
            parametres : this.feuille_de_temps,
            date_debut : this.informations_dates.debut,
            date_fin : this.informations_dates.fin,
            mode_affichage : this.mode_affichage,
        };

        if(element_id != null)
            data.element_id = element_id;

        $.post({
            url : '{{route('saisie_des_temps.chargement_donnees',['type_saisie' => $type_saisie], false)}}',
            dataType:'json',
            data : data
        }).done(async (donnees) => {

            this.commentaires = donnees.commentaires;

            if(element_id == null)
                this.utilisateur_a_valider = donnees.utilisateur_a_valider;

            this.traitement_retour_chargement_donnees(donnees,element_id);

            this.parametrage_date = donnees.parametrage_date;

            this.loader_element = false;
        });
    },

    enregistre_temps : async function(feuille_de_temps,donnees_pour_enregistrement){

        var route = 'eden/element/feuille_de_temps/creer';

        if(feuille_de_temps.id == null && feuille_de_temps[this.champ_de_duree] == 0)
            return;

        this.enregistrement_en_cours = donnees_pour_enregistrement.categorie_id || donnees_pour_enregistrement.activite_id ? donnees_pour_enregistrement.element_id : 1;

        var methode = 'POST';

        if(feuille_de_temps.id != undefined){

            if(feuille_de_temps[this.champ_de_duree] != 0)
                route = 'eden/element/feuille_de_temps/'+feuille_de_temps.id+'/enregistrer';
            else{
                route = 'eden/element/feuille_de_temps/'+feuille_de_temps.id+'/supprimer';
                donnees_pour_enregistrement = {};
                methode = 'GET';
            }
        }

        var donnees = await $.ajax({
            url : route,
            data: donnees_pour_enregistrement,
            method : methode
        });

        if(donnees.retour !== true) {

            erreur(donnees.retour);
            this.enregistrement_en_cours = null;
            return false;
        }

        this.enregistre_parametrage_date();

        var donnees_retour = donnees.element;

        if(feuille_de_temps[this.champ_de_duree] == 0){
            donnees_retour = {};

            donnees_retour[this.champ_de_duree] = 0;
        }

        this.$emit('changement_valeur');

        return donnees_retour;
    },

    enregistre_parametrage_date : function(){

        if(this.parametrage_date != null || this.desactiver_enregistrer_elements_selectionnes !== true)
            return;

        var elements_ids = [];

        for(element_disponible of this.elements){

            elements_ids.push(element_disponible.id.toString());
        }

        $.post({
            url : 'eden/element/sdt_date_elements_selectionnes/creer',
            data: {
                date_debut : this.informations_dates.debut,
                date_fin : this.informations_dates.fin,
                utilisateur_id : this.feuille_de_temps.utilisateur_id,
                type_element : this.feuille_de_temps.type_element,
                elements_ids : JSON.stringify(elements_ids),
                mode_affichage : this.mode_affichage,
            },
        }).done((donnees) => {
            this.parametrage_date = donnees.element;
        });
    },

    desactiver_element : async function(element,index_element){

        if(!await confirm_eden(this.$root.traduction('interface.saisie_des_temps.confirmation_desactivation')))
            return;

        var elements_ids = [];

        for(element_disponible of this.elements){

            if(element_disponible.id != element.id)
                elements_ids.push(element_disponible.id);
        }

        var data = {
            element_id : element.id,
            elements_ids : elements_ids,
            parametres : this.feuille_de_temps,
            date_debut : this.informations_dates.debut,
            date_fin : this.informations_dates.fin,
            mode_affichage : this.mode_affichage,
            type_saisie : '{{$type_saisie}}',
        };

        $.post({
            url : '{{route('saisie_des_temps.desactiver_element', [], false)}}',
            data: data,
        }).done((donnees) => {

            this.parametrage_date = donnees.parametrage_date;
            this.elements.splice(index_element,1);
            this.feuille_de_temps.elements_ids.splice(this.feuille_de_temps.elements_ids.indexOf(element.id),1);
            this.$emit('changement_valeur');
        });
    },

    enregistre_commentaire : async function(commentaire,donnees_pour_enregistrement){

        var route = 'eden/element/feuille_de_temps_commentaire/creer';

        var methode = 'POST';

        if(commentaire.id != undefined){

            if(commentaire.commentaire != '' && commentaire.commentaire != null)
                route = 'eden/element/feuille_de_temps_commentaire/'+commentaire.id+'/enregistrer';
            else{
                route = 'eden/element/feuille_de_temps_commentaire/'+commentaire.id+'/supprimer';

                donnees_pour_enregistrement = {};
                methode = 'GET';
            }
        }

        var donnees = await $.ajax({
            url : route,
            data: donnees_pour_enregistrement,
            method : methode
        });

        if(donnees.retour !== true) {

            erreur(donnees.retour);
            this.enregistrement_en_cours = null;
            return false;
        }

        var donnees_retour = donnees.element;

        if(commentaire.commentaire == '' || commentaire.commentaire == null)
            donnees_retour = {
                commentaire : null
            };

        return donnees_retour;
    },

    corrige_virgule : function(modele,colonne){

        var valeur = modele[colonne];

        if (typeof valeur === 'string' || valeur instanceof String)
            this.$set(modele,colonne,valeur.replace(',','.'));
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    $.post({
        url:'/eden/elements/utilisateur',
        dataType:'json'
    }).done((utilisateurs) => {
        this.utilisateurs = utilisateurs;
    });
@endpush
