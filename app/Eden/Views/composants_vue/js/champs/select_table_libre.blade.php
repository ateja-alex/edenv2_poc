<script>
    const select_table_libre = Vue.component('select-table-libre', {
        template: `
            <div class="select_champs_libres">
                <div class="select" v-if="table_libre != null">
                    <div class="affichage" v-html="$root.traduction(table_libre.index_traduction+'.nom_table')+ ' ('+table_libre.type_element+')'"></div>
                    <span @click="$emit('changement_select_table_libre',null);recherche = '';">
                        <i class="fas fa-times css_pointer"></i>
                    </span>
                </div>
                <div class="select" v-click_outside="" v-else>
                    <input type="text" v-model="recherche" @input="liste_table = true">
                    <span @click="liste_table = !liste_table">
                        <i :class="'fas fa-chevron-'+(liste_table ? 'up' : 'down')"></i>
                    </span>
                    <div v-if="liste_table" class="liste_champ">
                        <template v-for="table in tables_libres_liste">
                            <div class="valeur" v-html="$root.traduction(table.index_traduction+'.nom_table') + ' ('+table.type_element+')'" @click="selection_table(table)"></div>
                        </template>
                    </div>
                </div>
            </div>
        `,
        props: {
            tables_libres : {
                type : Array,
                default: function(){
                    return [];
                }
            },
            type_element:{
                type : String,
                default : null
            },
        },
        data : function(){
            return {
                recherche : null,
                liste_table : false,
            };
        },
        methods : {
            selection_table(table_libre){

                this.$emit('changement_select_table_libre', table_libre);

                this.liste_table = !this.liste_table;
            },
        },
        computed: {
            table_libre : function(){

                var tables_libres = this.tables_libres.filter(table => table.type_element == this.type_element);

                if(tables_libres.length == 0)
                    return null;

                return tables_libres[0];
            },
            tables_libres_liste : function(){

                var tables_libres = structuredClone(this.tables_libres);

                if(this.recherche != null && this.recherche != '') {

                    var recherche = this.$options.filters.retraite_caracteres_speciaux(this.recherche.toLowerCase());

                    tables_libres = tables_libres.filter(
                        table_libre => this.$options.filters.retraite_caracteres_speciaux(this.$root.traduction(table_libre.index_traduction + '.nom_table').toLowerCase()).includes(recherche) || this.$options.filters.retraite_caracteres_speciaux(table_libre.type_element).includes(recherche)
                    );
                }

                return tables_libres;
            },
        },
        mounted : async function() {},
        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (!(el.contains(event.target)))
                            vnode.context.liste_table = false;
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