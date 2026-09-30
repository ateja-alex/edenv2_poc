<script>
    const input_parametrage = Vue.component('input-parametrage', {
        template: `<div class="input_parametrage_flex">
          <div class="input_parametrage_contenteditable" :id="this.id_random" contenteditable="true" @blur="insertion_input_hidden" v-show="!visualisation_admin"></div>
            <input class="input_parametrage_hidden_input" type="text" @change="retranscription_atwho" v-show="visualisation_admin" :name="name" v-model="vmodel[name]">
          <span v-if="type_utilisateur === 2" data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-10 mr-3" data-original-title="Mode développement" @click="visualisation_admin = !visualisation_admin" ><i class="css_action_icon fa fa-fw fa-pencil" aria-hidden="true"></i></span>
        </div>`,
        props: {
            at_custom: {
                type: String,
                default: '',
            },
            vmodel: {
                type: Object,
                default: '',
            },
            donnees: {
                type: Array,
                default: [],
            },
            name: {
                type: String,
                default: '',
            },
            type_utilisateur: {
                type: Number,
                default: null,
            }
        },

        data: function(){
            return {
                visualisation_admin : false,
            }
        },
        computed: {

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
        mounted: function() {

            var at_who_object = {
                at: this.at_custom,
                data: this.donnees,
                lookUpOnClick: false,
                limit: this.donnees.length,
                suffix: '',
            };

            at_who_object.insertTpl = '<div id_nom="${id}" class="input-parametrage-resultat">${name}<i onclick="$(event.target).parent().remove()" class="fas fa-times"></i></div>';

            $('#' + this.id_random).atwho(at_who_object);

            this.retranscription_atwho();
        },
        methods: {
            insertion_input_hidden : function(){

                const contenu_div = $('#'+this.id_random).clone();

                var input_resultat = contenu_div.find('.input-parametrage-resultat');

                $(input_resultat).each(function() {
                    var valeur_id_nom = $(this).attr('id_nom');
                    $(this).parent().replaceWith(valeur_id_nom);
                });

                this.vmodel[this.name] = contenu_div.text();

            },

            retranscription_atwho : function(){
                var chaine_existante = this.vmodel[this.name] ?? '';

                if(chaine_existante == null || chaine_existante == '')
                    return;

                document.getElementById(this.id_random).innerHTML = "";

                const regex = /#([^\s#]+)#/g;

                var id_recup;
                var nom_recup;

                var positions=[];
                var match;

                while ((match = regex.exec(chaine_existante)) !== null){
                    var mot = match[0];
                    var position = [match.index,match.index + match[0].length];

                    positions.push({
                        name: mot,
                        index: position
                    });
                }

                var tableau_chaine = [];
                var last_index = 0;

                positions.forEach(element => {
                    if(element.index[0] !== last_index) {
                        tableau_chaine.push({
                            text: true,
                            valeur: chaine_existante.substring(last_index, element.index[0])
                        });
                    }
                    tableau_chaine.push({
                        text: false,
                        valeur: element.name
                    });

                    last_index = element.index[1];
                });

                if(last_index < chaine_existante.length) {
                    tableau_chaine.push({
                        text: true,
                        valeur: chaine_existante.substring(last_index)
                    });
                }

                tableau_chaine.forEach(element => {
                    if(element.text === true){
                        document.getElementById(this.id_random).insertAdjacentText("beforeend", element.valeur);
                    } else {

                        var nom_donnee = false;
                        for(donnee of this.donnees){
                            if(donnee.id === element.valeur){
                                nom_donnee = donnee.name;
                                break;
                            }
                        }

                        if(nom_donnee !== false){
                            id_recup = element.valeur;
                            nom_recup = nom_donnee;

                            valeur_interprete = element.valeur.replace(id_recup,`<span class="atwho-inserted" data-atwho-at-query="${this.at_custom}" contenteditable="false"> <div id_nom="${id_recup}" class="input-parametrage-resultat">${nom_recup}<i onclick="$(event.target).parent().remove()" class="fas fa-times"></i></div></span>`);

                            document.getElementById(this.id_random).insertAdjacentHTML("beforeend", valeur_interprete);
                        }else {
                            document.getElementById(this.id_random).insertAdjacentText("beforeend", element.valeur);
                        }

                    }
                });
            }
        },
        watch: {
            donnees: function(newVal, oldVal) {
                var at_who_object = {
                    at: this.at_custom,
                    data: this.donnees,
                    lookUpOnClick: false,
                    limit: this.donnees.length,
                    suffix: '',
                };

                at_who_object.insertTpl = '<div id_nom="${id}" class="input-parametrage-resultat">${name}<i onclick="$(event.target).parent().remove()" class="fas fa-times"></i></div>';

                $('#' + this.id_random).atwho(at_who_object);

                this.retranscription_atwho();
            }
        }
    })
</script>