<script>
    const champ_note = Vue.component('champ-note', {
        template: `<span style="display: flex;padding: 5px;gap: 5px;">
                    <input type="hidden" :value='valeur_note' :name="name"/>
                    <span v-for="i in 5" :key="i" :class="parametres.logo" :style="{ color: valeur_note >= i ? parametres.couleur_plein : parametres.couleur_vide, cursor: lecture_seule ? 'default' : 'pointer' }" @click="setValeur(i)"></span>
                  </span>`,


        props: {
            modele: {},
            nom_sql: '',
            value: {
                type: Number | String,
                default: null,
            },
            name: {
                type: String,
                default: '',
            },
            lecture_seule: {
                type: Boolean | Number,
                default: false,
            },
            parametres: {
                type: Object,
                default: () => ({
                    logo: 'fa fa-star',
                    couleur_plein: '#fbd71c',
                    couleur_vide: '#aaa'
                }),
            },
        },

        computed: {
            valeur_note: {
                get() {
                    if (this.value !== null) {
                        return this.value;
                    } else if (this.modele && this.nom_sql in this.modele) {
                        return this.modele[this.nom_sql];
                    }
                    return 0;
                },
                set(val) {
                    if (this.value !== null) {
                        this.$emit('input', val);
                    } else if (this.modele && this.nom_sql) {
                        this.$set(this.modele, this.nom_sql, val);
                    }
                }
            }
        },

        methods: {
            setValeur(i) {
                if(!this.lecture_seule) {
                    this.valeur_note = (this.valeur_note === i) ? 0 : i;
                }
            }
        }
    })
</script>
