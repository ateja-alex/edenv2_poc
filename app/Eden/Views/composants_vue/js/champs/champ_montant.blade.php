<script>
    const champ_montant = Vue.component('champ-montant', {
        template: `<div>
            <input :class="class_input" :min="valeur_uniquement_positive ? 0 : null" :disabled="lecture_seule" :style="style_input" type="number" :name="name" v-model="valeur" @change="changement_valeur" :placeholder="placeholder" :step="1 / (10 ** nombre_decimale)" autocomplete="off" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
        </div>`,
        props: {

            modele: {},
            nom_sql: '',
            name: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
            placeholder : {
                type : String,
                default: '',
            },
            nombre_decimale : {
                type : Number | Boolean,
                default: false,
            },
            class_input : {
                type : String,
                default: '',
            },
            style_input : {
                type : String,
                default: '',
            },
            parametres_emit : {
                type : Object,
                default : function(){
                    return {};
                }
            },
            valeur_uniquement_positive: {
                type : Boolean,
                default : false,
            },
            valeur_non_vide: {
                type : Boolean,
                default : false,
            },
            champ_entier: {
                type : Number | Boolean,
                default: false,
            }
        },

        computed: {

            valeur : {

                get() {
                    return this.modele[this.nom_sql];
                },
                set(nouvelle_valeur) {

                    if (nouvelle_valeur == '' || ((nouvelle_valeur >= 0 && this.valeur_uniquement_positive) || !this.valeur_uniquement_positive))
                        this.modele[this.nom_sql] = nouvelle_valeur;
                }
            }
        },

        created : function(){

            this.initialisation_valeur();
        },

        methods: {

            changement_valeur : function(){

                if(this.modele[this.nom_sql] == '' && this.valeur_non_vide)
                    this.modele[this.nom_sql] = 0;

                this.modele[this.nom_sql] = parseFloat(this.modele[this.nom_sql]);

                if(this.nombre_decimale !== false && this.champ_entier == false)
                    this.modele[this.nom_sql] = this.modele[this.nom_sql].toFixed(this.nombre_decimale);
                else if(this.champ_entier == true) 
                    this.modele[this.nom_sql] = this.modele[this.nom_sql].toFixed(0);

                this.montant_tmp = false;

                var parametres_emit = this.parametres_emit;

                parametres_emit.modele = this.modele;
                parametres_emit.nom_sql = this.nom_sql;

                this.$parent.$emit('maj_champ_montant',parametres_emit);
            },

            initialisation_valeur : function() {

                if(this.modele[this.nom_sql] == null || this.modele[this.nom_sql] == '')
                    return;

                var valeur = parseFloat(this.modele[this.nom_sql]);

                if (this.nombre_decimale !== false && this.champ_entier == false)
                    valeur = valeur.toFixed(this.nombre_decimale);
                else if(this.champ_entier == true)
                    valeur = valeur.toFixed(0);

                if(this.modele[this.nom_sql] !== valeur)
                    this.modele[this.nom_sql] = valeur;
            }
        },
        watch: {
            modele : function(){

                this.initialisation_valeur();
            },
        }
    });
</script>