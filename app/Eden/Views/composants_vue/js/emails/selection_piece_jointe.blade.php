<script>
    const selection_piece_jointe = Vue.component('selection-piece-jointe', {
        template: `
            <div class="selection_piece_jointe">
                <div v-click_outside="">
                    <i ref="bouton_ajout" class="css_action_icon fas fa-plus" @click="chargement_selection()"></i>
                    <dropdown v-if="selection_choix">
                        <div @click="$refs.champ_dropzone.$refs.input.click();selection_choix=false;">
                            <i class="fas fa-plus"></i> @traduction('composant.emails.selection_piece_jointe.ajouter_piece_jointe')
                        </div>
                        <div class="titre" v-if="propositions_filtres.length > 0">
                            @traduction('composant.emails.selection_piece_jointe.pieces_jointes_suggerees')
                        </div>
                        <div v-for="proposition in propositions_filtres" @click.stop="choix_proposition(proposition)">
                            <span v-html="proposition.affichage_select ?? proposition.valeur"></span>
                        </div>
                    </dropdown>
                    <champ-dropzone ref="champ_dropzone" @upload_fichiers="ajout_fichiers($event)" v-show="false"></champ-dropzone>
                </div>
                <div class="piece_jointe" :style="'background: '+( couleurs_groupes[piece_jointe.groupe_id] ?? 'grey')" v-for="(piece_jointe,index_piece_jointe) in modele">
                    <a v-if="piece_jointe.affichage" class="lien_piece_jointe" target="_blank" :href="piece_jointe.valeur">
                        <i class="fas fa-paperclip"></i>
                        <span v-html="piece_jointe.affichage"></span>
                    </a>
                    <span v-else v-html="piece_jointe.valeur"></span>
                    <i class="css_pointer fas fa-times" v-if="!piece_jointe.obligatoire" @click.stop="suppresion_modele(index_piece_jointe)"></i>
                </div>
            </div>
        `,
        props : {
            type_element : {
                type : String,
                required : true,
            },
            elements : {
                type : Array,
                default : function(){
                    return [];
                },
            },
            modele : {
                type : Array,
                default : function(){
                    return [];
                },
            },
            defaut : {
                type : Array,
                default : function(){
                    return [];
                },
            },
            parametres_destinataires : {
                type : Object,
                default : function(){
                    return {};
                },
            },
            couleurs_groupes : {
                type : Object,
                default : function(){
                    return {};
                },
            },
        },
        data:function(){
            return{
                selection_choix : false,
                propositions : [],
            }
        },
        methods : {
            choix_proposition : function(proposition){

                var nouvelle_liste = [...this.modele];
                nouvelle_liste.push(proposition);

                this.$emit('maj_modele', {pieces_jointes : nouvelle_liste});
                this.selection_choix = false;
            },

            suppresion_modele : async function(index){
                this.$emit('maj_modele', {pieces_jointes : this.modele.filter((mod,idx) => idx != index)});
                this.selection_choix = false;
            },

            chargement_selection : function(){

                $.post({
                    url : 'eden/email/chargement/pieces_jointes',
                    dataType : 'json',
                    data : {
                        groupes_ids : [
                            this.elements.map(e => e.id)
                        ],
                        parametres : {
                            niveau : 1,
                            ...this.parametres_destinataires,
                        }
                    },
                }).done(async (data) => {

                    this.propositions = this.elements.length > 0 ? data.valeurs[0] : data.valeurs_globales;

                    if(this.propositions_filtres.length == 0)
                        this.$refs.champ_dropzone.$refs.input.click();
                    else
                        this.selection_choix = true;
                });
            },

            ajout_fichiers : function(fichiers){

                var nouvelle_liste = [...this.modele];
                nouvelle_liste = nouvelle_liste.concat(fichiers.map((f) => {
                    return {
                        affichage:f.nom_original,
                        obligatoire:false,
                        valeur:f.url_storage.replace('public/','storage/'),
                    };
                }));

                this.$emit('maj_modele', {pieces_jointes : nouvelle_liste});
                this.selection_choix = false;
                this.$refs.champ_dropzone.fichiers = [];
                this.$refs.champ_dropzone.$refs.input.value = null;

            },
        },
        computed : {
            propositions_filtres : function(){

                return this.defaut
                    .concat(this.propositions.filter(p => !this.defaut.map(d => d.valeur).includes(p.valeur)))
                    .filter(p => !this.modele.map(m => m.valeur).includes(p.valeur));
            },
        },
        directives : {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (event.target.parentElement != null && !(el.contains(event.target)) && vnode.context.selection_choix)
                            vnode.context.selection_choix = false;
                    };
                    document.body.addEventListener('click', el.clickOutsideEvent)
                },
                unbind: function (el) {
                    document.body.removeEventListener('click', el.clickOutsideEvent)
                },
            }
        }
    });
</script>