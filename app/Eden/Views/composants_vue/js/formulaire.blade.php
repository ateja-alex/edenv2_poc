 <script>
const formulaire = Vue.component('formulaire', {
    template: `<div>
        @include('eden::formulaires.dedoublonnage')
        <div style="display: flex;align-items: center;justify-content: center;height: 150px;" v-if="chargement_formulaire">
            <img style="width: 60px;" src="{{'eden/images/ajax_loader.gif'}}">
        </div>
        <form v-else-if="form" class="css_form" action="#" :id="'formulaire_'+id_random">
            <component ref="formulaire" :key="cle_composant" :is="formulaire" />
        </form>
        <component v-else ref="formulaire" :key="cle_composant" :is="formulaire" />
    </div>`,
    props:{
        nom_formulaire: {
            type: String,
            default : null
        },
        contexte: {
            type: String,
            default : null
        },
        uniquement_champs_editables: {
            type: Boolean,
            default : false,
        },
        options: {
            type: Object,
            default() {
                return {};
            }
        },
        form : {
            type: Boolean,
            default: true,
        }
    },
    data: function(){
        return {
            formulaire : {
                template : '<div></div>',
                name: 'affichage_formulaire'
            },
            element : {},
            element_initial : {},
            erreur: false,
            cle_composant: 0,
            chargement_formulaire: false,
            type_element: null,
            vmodel: null,
            champs_obligatoires : {},
            champs_type_element : {},
            valeurs_par_defaut: {},
            champs_formulaire: [],
            table_libre: {},
            @yield('donnees_pour_vuejs_data')
            @stack('donnees_pour_vuejs_data')
        }
    },
    methods:{
        recuperation_formulaire : async function(){

            var instance = this;

            if(instance.nom_formulaire == null)
                return;

            this.chargement_formulaire = true;
            var donnees_formulaire = instance.donnees_formulaire;
            var chaine_identitfication_formulaire = Object.values(donnees_formulaire).join('_');
            instance.erreur = false;

            if(this.$root.cache_formulaires[chaine_identitfication_formulaire] !== undefined){

                instance.retour_appel(this.$root.cache_formulaires[chaine_identitfication_formulaire]);
                return;
            }

            await $.post({
                url: '{{route('base_eden.formulaire.affichage', [], false)}}',
                contentType: 'application/json',
                dataType : 'json',
                data : JSON.stringify(donnees_formulaire),
            }).done(function(donnees){

                instance.retour_appel(donnees);
                instance.$root.cache_formulaires[chaine_identitfication_formulaire] = donnees;
            });
        },

        reinitialisation_modele: function(){

            this.element = structuredClone(this.element_initial);

            if(this.$refs.formulaire != undefined && this.$refs.formulaire[this.vmodel] != undefined)
                this.$set(this.$refs.formulaire,this.vmodel,this.element);
        },

        actualisation_modele: async function(){

            await $.get({
                url : '/eden/element/' + this.type_element + '/' + this.element.id,
                dataType: "json",
                method: 'GET'
            }).done((element) => {

                this.$set(this,'element', element);
                this.$set(this.$refs.formulaire,this.vmodel, this.element);
            });
        },

        retour_appel : function(donnees){

            var instance = this;

            instance.$parent.$emit('enregistrement_disponible',!donnees.erreur);

            if(donnees.erreur !== false) {
                instance.formulaire = {
                    template: '<div>' + donnees.erreur + '</div>'
                };
                instance.erreur = true;
                instance.chargement_formulaire = false;
                return;
            }

            instance.element_initial = JSON.parse(JSON.stringify(donnees.modele_par_defaut));

            if(instance.$parent !== undefined && typeof instance.$parent.recuperation_modele_par_defaut == 'function')
                instance.element = instance.$parent.recuperation_modele_par_defaut(JSON.parse(JSON.stringify(donnees.modele_par_defaut)));
            else if(instance.$parent.$parent !== undefined && typeof instance.$parent.$parent.recuperation_modele_par_defaut == 'function')
                instance.element = instance.$parent.$parent.recuperation_modele_par_defaut(JSON.parse(JSON.stringify(donnees.modele_par_defaut)));
            else
                instance.element = JSON.parse(JSON.stringify(donnees.modele_par_defaut));

            this.valeurs_par_defaut = donnees.valeurs_par_defaut;

            for(nom_sql in donnees.valeurs_par_defaut){
                instance.element_initial[nom_sql] = donnees.valeurs_par_defaut[nom_sql];
                instance.element[nom_sql] = donnees.valeurs_par_defaut[nom_sql];
            }

            instance.cle_composant++;
            eval(donnees.formulaire);
            instance.chargement_formulaire = false;
            instance.type_element = donnees.type_element;
            instance.vmodel = donnees.vmodel;
            instance.champs_obligatoires = donnees.champs_obligatoires;
            instance.champs_type_element = donnees.champs_type_element;
            instance.table_libre = donnees.table_libre;
            instance.champs_formulaire = donnees.champs_formulaire;
            instance.$forceUpdate();
            instance.$parent.$emit('formulaire_charger',instance.nom_formulaire);

            instance.$nextTick(() => {
                formulaire.name = 'affichage_formulaire';
                instance.formulaire = formulaire;

                instance.$nextTick(() => {

                    $("textarea").mention({
                        queryBy: ['name', 'username'],
                        users: instance.$root.utilisateurs_pour_mention
                    });

                });
            });
        },

        vider_cache:function(){
            var chaine_identitfication_formulaire = Object.values(this.donnees_formulaire).join('_');

            delete this.$root.cache_formulaires[chaine_identitfication_formulaire];

            this.$forceUpdate();
        },

        enregistrer : async function(informations_supplementaires = {},url = null, type_enregistrement = 0){

            var element_id = this.element.id;
            var type_element = this.type_element;
            var enregistrement = element_id != '' && element_id != 0 && element_id != null;

            if(url == null) {
                if (enregistrement)
                    url = "eden/element/" + type_element + "/" + element_id + "/enregistrer";
                else
                    url = "eden/element/" + type_element + "/creer";
            }

            var informations = this.formulaire_donnees_renseignes(informations_supplementaires);

            var champ_non_remplis = this.verification_champs_obligatoires(informations);

            if(champ_non_remplis.length > 0){

                var nom_champ_non_remplis = champ_non_remplis.map(function(champ_obligatoire){
                    return champ_obligatoire.nom;
                });

                var erreur_affichage = '';

                if(champ_non_remplis.length == 1)
                    erreur_affichage = this.$root.traduction('messages.php.champ_obligatoire')+nom_champ_non_remplis.join(', ');
                else
                    erreur_affichage = this.$root.traduction('messages.php.champs_obligatoires')+nom_champ_non_remplis.join(', ');

                await erreur(erreur_affichage);

                this.indication_champs_obligatoires(champ_non_remplis);

                return {
                    retour : false,
                };
            }

            if(typeof this.$refs.formulaire.confirmation_enregistrement !== "undefined") {
                var retour_confirmation = await this.$refs.formulaire.confirmation_enregistrement(informations);

                if(retour_confirmation !== true)
                    return retour_confirmation;
            }

            var retour = await $.post({
                url: url,
                data: informations,
                dataType: "json",
            }).done(async (donnees) => {

                if (donnees.retour !== true) {

                    await erreur(donnees.retour);
                    return;
                }

                if(!enregistrement)
                    this.$root.$emit('ajout_element', type_element);
                else
                    this.$root.$emit('enregistrement_formulaire', {
                        nom_formulaire : this.nom_formulaire,
                        retour : donnees,
                    });

                _.extend(this.element, donnees.element);

                if(type_enregistrement != undefined && type_enregistrement !== 0) {

                    await this.reinitialisation_modele();
                    this.$parent.$emit('formulaire_charger',this.nom_formulaire);

                    if (type_enregistrement === 2) {

                        var element = structuredClone(donnees.element);
                        element.id = '';

                        this.element = {...this.element, ...element};

                        this.$emit('enregistrement_et_duplication', donnees.element);
                    }
                }
            });

            return retour;
        },
        test_enregistrer : async function(informations_supplementaires = {}) {

            var element_id = this.element.id;
            var type_element = this.type_element;

            var informations = this.formulaire_donnees_renseignes(informations_supplementaires);

            var champ_non_remplis = this.verification_champs_obligatoires(informations);

            if(champ_non_remplis.length > 0){

                var nom_champ_non_remplis = champ_non_remplis.map(function(champ_obligatoire){
                    return champ_obligatoire.nom;
                });

                var erreur_affichage = '';

                if(champ_non_remplis.length == 1)
                    erreur_affichage = this.$root.traduction('messages.php.champ_obligatoire')+nom_champ_non_remplis.join(', ');
                else
                    erreur_affichage = this.$root.traduction('messages.php.champs_obligatoires')+nom_champ_non_remplis.join(', ');

                return erreur_affichage;
            }

            var retour = await $.ajax({
				method: 'POST',
				url: '/eden/element/'+type_element+'/'+element_id+'/test_enregistrement',
				dataType: "json",
				data: informations,
				proccessData: false,
			});

            return retour.retour;
        },
        formulaire_donnees_renseignes : function(informations_supplementaires,form = null){

            if(form == null)
                form = $('#formulaire_'+this.id_random);

            var id_random = this.id_random;

            var informations = form.serializeArray();

            for(nom_sql in this.valeurs_par_defaut){

                if(!informations.map(champ => champ.name).includes(nom_sql))
                    informations_supplementaires[nom_sql] = this.valeurs_par_defaut[nom_sql];
            }

            for(index in informations_supplementaires){

                if(!informations.map(champ => champ.name).includes(index) && !informations.map(champ => champ.name).includes(index+'[]')){

                    var valeur = informations_supplementaires[index];

                    if (Array.isArray(valeur)) {

                        for (sous_valeur of valeur) {
                            informations.push({
                                name: index + '[]',
                                value: sous_valeur
                            });
                        }
                    } else {

                        informations.push({
                            name: index,
                            value: valeur
                        });
                    }
                }
            }

            return informations;
        },
        verification_champs_obligatoires: function(informations){

            var informations_formatees = {};

            for(information of informations){

                informations_formatees[information.name] = information.value;
            }

           var champs_obligatoires_non_remplis = [];

           for(champ_obligatoire of this.champs_obligatoires){

               if(champ_obligatoire.condition != undefined) {
                   var condition = champ_obligatoire.condition.replace(new RegExp(this.vmodel+"\\.", "g"),'element.');

                   with(this) { 
                        condition = eval(condition); 
                    }

                   if(condition != true)
                       continue;
               }

               var valeur_a_verifier = informations_formatees[champ_obligatoire.nom_sql];

               if(valeur_a_verifier == undefined)
                   valeur_a_verifier = this.element[champ_obligatoire.nom_sql];

               if(this.verification_champ_rempli(valeur_a_verifier,champ_obligatoire))
                   champs_obligatoires_non_remplis.push(champ_obligatoire);
           }

           var formulaire_enfant = this.$refs.formulaire;

           if(formulaire_enfant.sous_formulaires_par_type_element == undefined)
               return champs_obligatoires_non_remplis;

            var composants_enfants = formulaire_enfant.$children;

            var sous_formulaires_vue = [];

            for(composant_enfant of composants_enfants){

                if(composant_enfant.sous_formulaire)
                    sous_formulaires_vue.push(composant_enfant.$options.name);

            }

           // Gestion des sous formulaires
            for(sous_formulaires of Object.values(formulaire_enfant.sous_formulaires_par_type_element)){

                for(cle_sous_formulaire in sous_formulaires){

                    var sous_formulaire = sous_formulaires[cle_sous_formulaire];

                    if(!sous_formulaires_vue.includes(sous_formulaire.name))
                        continue;

                    var sous_formulaire_retraite = formulaire_enfant.sous_formulaires_retraite[sous_formulaire.nom_sous_formulaire];

                    var prefixe_sous_formulaire = "<br>"+this.$root.traduction("formulaire." + sous_formulaire.nom_formulaire_parent + ".sous_formulaire." + sous_formulaire.nom_sous_formulaire + ".titre");

                    if(cle_sous_formulaire > 0)
                        prefixe_sous_formulaire += " "+cle_sous_formulaire;

                    prefixe_sous_formulaire += " => ";

                    var prefixe_ajouter = false;

                    for(champ_obligatoire of sous_formulaire_retraite.champs_obligatoires){

                        var champ_obligatoire_clone = structuredClone(champ_obligatoire);

                        if(champ_obligatoire_clone.condition != undefined) {
                           var condition = champ_obligatoire_clone.condition
                               .replace(new RegExp(sous_formulaire_retraite.type_element_enfant+"\\.", "g"),'element.'+sous_formulaire.type_element_remplacement+".");

                            with(this) { 
                                condition = eval(condition); 
                            }

                           if(condition != true)
                               continue;
                        }

                        champ_obligatoire_clone.name_remplacement_js = sous_formulaire.name_remplacement_js;

                        var nom_dans_formulaire = champ_obligatoire_clone.name_remplacement_js+'['+champ_obligatoire_clone.nom_sql+']';

                        var valeur_a_verifier = informations_formatees[nom_dans_formulaire];

                        if(valeur_a_verifier == undefined)
                           valeur_a_verifier = this.element[sous_formulaire.type_element_remplacement][champ_obligatoire_clone.nom_sql];

                        if(this.verification_champ_rempli(valeur_a_verifier,champ_obligatoire_clone)) {

                            if(prefixe_ajouter == false) {
                                champ_obligatoire_clone.nom = prefixe_sous_formulaire+champ_obligatoire_clone.nom;
                                prefixe_ajouter = true;
                            }

                            champs_obligatoires_non_remplis.push(champ_obligatoire_clone);
                        }
                    }
                }
            }

           return champs_obligatoires_non_remplis;
        },
        indication_champs_obligatoires : function(champs_obligatoires_non_remplis, form = null){

            if(form == null)
                form = $('#formulaire_'+this.id_random);

            form.find('.js_erreurs_champs_obligatoire').removeClass( "css_erreurs_champs_obligatoire js_erreurs_champs_obligatoire" );
            
            if (champs_obligatoires_non_remplis.length > 0) {

                champs_obligatoires_non_remplis.map(function(champ_obligatoire){

                    if(champ_obligatoire.name_remplacement_js != undefined)
                        var nom_sql = champ_obligatoire.name_remplacement_js + '_' + champ_obligatoire.nom_sql;
                    else
                        var nom_sql = champ_obligatoire.nom_sql;

                    form.find('.formulaire_champ_'+nom_sql).addClass('js_erreurs_champs_obligatoire css_erreurs_champs_obligatoire');
                });
           }
        },
        verification_champ_rempli : function(valeur_a_verifier,champ_obligatoire){

            var champ_non_rempli = false;

            var valeur_a_verifier = structuredClone(valeur_a_verifier);

           if(champ_obligatoire.type == 2 || champ_obligatoire.type == 3 || champ_obligatoire.zero_possible) {

               if(['', null, false, undefined].includes(valeur_a_verifier))
                    champ_non_rempli = true;
           }
           else if([10,11,12].includes(champ_obligatoire.type)){

               valeur_a_verifier = Object.values(valeur_a_verifier);

               if(!Array.isArray(valeur_a_verifier) || valeur_a_verifier.length == 0)
                   champ_non_rempli = true;
           }
           else if(champ_obligatoire.type == 15){
               if([0,'0','', null, false, undefined,'[]'].includes(valeur_a_verifier))
                    champ_non_rempli = true;
           }
           else if(champ_obligatoire.type == 16){
                if(Object.values(valeur_a_verifier).length == 0)
                    champ_non_rempli = true;
           }
           else if([0,'0','', null, false, undefined].includes(valeur_a_verifier))
               champ_non_rempli = true;

           return champ_non_rempli;
        },
        @yield('donnees_pour_vuejs_methods')
        @stack('donnees_pour_vuejs_methods')
    },
    mounted: async function() {
        await this.recuperation_formulaire();
        @yield('donnees_pour_vuejs_mounted')
        @stack('donnees_pour_vuejs_mounted')
    },
    computed :{
        donnees_formulaire : function(){

            var donnees_formulaire = {
                nom_formulaire : this.nom_formulaire
            };
            if(this.contexte !== null)
                donnees_formulaire['contexte'] = this.contexte;

            if(this.options !== null)
                donnees_formulaire['options'] = this.options;

            if(this.uniquement_champs_editables !== false)
                donnees_formulaire['uniquement_champs_editables'] = this.uniquement_champs_editables;

            return donnees_formulaire;
        },
        id_random: function() {
			length = 15;
			var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');
			if (! length) {
				length = Math.floor(Math.random() * chars.length);
			}
			var str = '';
			for (var i = 0; i < length; i++) {
				str += chars[Math.floor(Math.random() * chars.length)];
			}
			return str;
		},
    },
    watch: {
        nom_formulaire: function() {
            this.recuperation_formulaire();
        },
        'element.id':function(){
            var instance = this;

            instance.cle_composant++;
            if(instance.$refs.formulaire != undefined && instance.$refs.formulaire[instance.vmodel] != undefined)
                instance.$set(instance.$refs.formulaire,instance.vmodel,instance.element);
        },
    }
});
</script>
