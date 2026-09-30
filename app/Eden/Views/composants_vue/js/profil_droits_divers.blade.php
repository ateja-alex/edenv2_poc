<script>
    const profil_droits_divers = Vue.component('profil-droits-divers', {
        template: `
            <span class="profil_droits_divers" v-click_outside="">
                <slot name="bouton" v-if="bouton" :gestion_profil="gestion_profil" :profil_droits_divers="profil_droits_divers"></slot>
                <div v-if="affichage_bloc || !bouton" :class="bouton ? 'gestion_bloc_popover' : 'gestion_profil_bloc'" >
                    <div v-if="chargement_termine" v-for="profil_type in profils_par_type" class="type_profil">
                        <div class="titre" v-if="profils_par_type.length > 1" v-html="profil_type.nom"></div>
                        <div class="valeurs" v-for="profil in profil_type.profils">
                            <label :for="'acces_'+profil.id" v-html="$root.traduction(profil.index_traduction+'.nom')"></label>
                            <input :disabled="requete_enregistrement !== false" type="checkbox" @change="debounce_changement" v-model="profil_droits_divers.profils" :id="'acces_'+profil.id" :value="profil.id">
                        </div>
                    </div>
                </div>
            </span>
        `,
        props: {
            type : {
                type : String,
                default: null
            },
            index: {
                type : String | Number,
                default: null
            },
            bouton: {
                type : Boolean,
                default: false
            },
            type_profil: {
                type : String,
                default: null
            }
        },
        data:function(){
            return {
                profil_droits_divers: {
                    profils : [],
                },
                affichage_bloc: false,
                chargement_termine: false,
                requete_enregistrement : false,
            }
        },
        methods:{
            gestion_profil: function(){

                this.affichage_bloc = !this.affichage_bloc;
            },
            ajout_profil : async function(){

                if(this.profil_droits_divers.id > 0)
                    var url = "eden/element/profil_droits_divers/"+this.profil_droits_divers.id+"/enregistrer";
                else
                    var url = "eden/element/profil_droits_divers/creer";

                // on enregistre les infos du champ libre
                this.requete_enregistrement = $.post({

                    url: url,
                    dataType: "json",
                    method: 'POST',
                    data: {
                        type : this.type,
                        index : this.index,
                        profils : this.profil_droits_divers.profils.length > 0 ? this.profil_droits_divers.profils : null,
                    },
                }).done((donnees) => {

                    if(donnees.retour !== true) {

                        erreur(donnees.retour);
                        this.requete_enregistrement = false;
                        return;
                    }

                    this.profil_droits_divers = donnees.element;

                    if(this.profil_droits_divers.profils == null)
                        this.profil_droits_divers.profils = [];

                    this.requete_enregistrement = false;
                });
            },

            debounce_changement: _.debounce(function () {
                this.ajout_profil();
            }, 1000)
        },
        computed: {
            profils_par_type : function(){

                if(this.$root.cache.profils == null)
                    return {};

                var profils = this.$root.cache.profils.sort((a,b) => (this.$root.traduction(a.index_traduction+'.nom') > this.$root.traduction(b.index_traduction+'.nom')) ? 1 : ((this.$root.traduction(b.index_traduction+'.nom') > this.$root.traduction(a.index_traduction+'.nom') ) ? -1 : 0));

                var profils_par_type = [];

                var profils_extranet = profils.filter((profil) => {return profil.extranet == 1;});
                var profils_eden = profils.filter((profil) => {return profil.extranet == null || profil.extranet == 0;});

                if(this.type_profil != 'extranet' && profils_eden.length > 0)
                    profils_par_type.push({
                        nom : 'Eden',
                        profils : profils_eden
                    });

                if(this.type_profil != 'eden' && profils_extranet.length > 0)
                    profils_par_type.push({
                        nom : 'Extranet',
                        profils : profils_extranet
                    });

                return profils_par_type;
            },
        },
        mounted: function(){

            if(this.$root.cache.profils == null){
                this.$set(this.$root.cache,'profils',[]);

                $.post({
                    url : 'eden/elements/profil',
                    dataType:'json',
                }).done((elements) => {
                    this.$set(this.$root.cache,'profils',elements);
                });
            }

            $.post({
                url : 'eden/elements/profil_droits_divers',
                dataType:'json',
                data:{
                    filtrage:[
                        {
                            champ : 'type',
                            condition : 'where',
                            valeur : this.type
                        },
                        {
                            champ : 'index',
                            condition : 'where',
                            valeur : this.index
                        }
                    ]
                }
            }).done((elements) => {
                if(elements.length > 0) {
                    this.profil_droits_divers = elements[0];

                    if(this.profil_droits_divers.profils == null)
                        this.profil_droits_divers.profils = [];
                }

                this.chargement_termine = true;
            });
        },
        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (!(el.contains(event.target)) && vnode.context.bouton)
                            vnode.context.affichage_bloc = false;
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