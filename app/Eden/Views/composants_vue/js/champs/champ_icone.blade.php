<script>
    const champ_icone = Vue.component('champ-icone', {
        template: 
            `<div>
                <input type="hidden" :name="name" v-model="modele[nom_sql]">
                <button type="button" class="btn btn-primary iconpicker-component" style="position:relative;">
                    <i :class="modele[nom_sql]"></i>
                    <span v-if="modele[nom_sql]" @click="modele[nom_sql] = ''" class="fa fa-times btn-primary" style="position:absolute;border-radius:10px;top:-10px;right:-10px;padding: 2px 5px;"></span>
                </button>
                <button type="button" id="selecteur_icone" class="icp icp-dd btn btn-primary dropdown-toggle categorie" :data-selected="modele[nom_sql]" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu"></div>
            </div>`,
        props: {

            modele: {},
            nom_sql: '',
            name: '',
            placeholder: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
            mappage: {},
        },
        mounted : function() {

            $('#selecteur_icone').iconpicker();

            $('#selecteur_icone').on('iconpickerSelected', (e) => {
                
                this.modele[this.nom_sql] = e.iconpickerValue;
            });
        }
    });
</script>
