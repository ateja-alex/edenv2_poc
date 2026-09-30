<script>
    const champ_liste_toggle = Vue.component('champ-liste-toggle', {
        template: /*html*/`
        <div>
            <label class="switch">
                <input :name="name" type="checkbox" @change="$parent.$emit('changement_valeur',{nom_sql : nom_sql, valeur : modele[nom_sql]})" v-model="modele[nom_sql]" :true-value="1" :false-value="0" :disabled="lecture_seule">
                <span class="slider round"></span>
            </label>
            <input type="hidden" :name="name" :value="modele[nom_sql]"/>
        </div>`,

        props: {
            modele: '',
            nom_sql: '',
            name: '',
            class_css_js: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
        }
    });

</script>