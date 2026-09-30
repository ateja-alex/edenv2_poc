<script>
const editeur_code = Vue.component('editeur-code', {
    template: `<div style="width: 100%;">
        <div class="css_bloc_edition_html" :id="id_dynamique"></div>
        <div v-show="editeur == null" style="position: absolute;top: 0;">
            <img style="width: 60px;" src="{{'eden/images/ajax_loader.gif'}}">
        </div>
    </div>
    `,
    props: {
        language : '',
        value: '',
    },
    data: function () {
        return {
            editeur: null,
        }
    },

    computed: {
        id_dynamique: function () {
            return 'editor-' + this.guidGenerator();
        },
    },

    mounted: function () {

        var instance = this;

        setTimeout(() => {
            instance.initialisation_editeur();
        },0);
    },

    methods: {
        guidGenerator: function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }

            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
        },
        initialisation_editeur:function(){

            this.editeur = monaco.editor.create(document.getElementById(this.id_dynamique), {
                value: this.value,
                language: this.language,
                theme: 'vs-dark'
            });


            this.editeur.getModel().onDidChangeContent((event) => {
                this.$emit('input', this.editeur.getValue());
            });
        },

    },

});
</script>
