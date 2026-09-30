<script>
    const rapport_indicateur = Vue.component('rapport-indicateur', {
        template: `
        <div>
            <div class="card-header">
                <h4>@{{ titre_rapport }}</h4>
            </div>
            <div class="card-body">
                <div class="css_actualisation_rapport_en_cours js_actualisation_rapport_en_cours" v-if="actualisation_rapport">
                    <img src="/eden/images/ajax_loader.gif" />
                </div>
                <div class="css_icon_donnees_indicateur" v-else>
                    <span v-if="icone_dans_rapport" :class="'fa ' + icone_dans_rapport" style="font-size: 50px;opacity: 0.5;"></span>

                    <a v-if="lien" :href="lien" target="_blank" style="color: white;display: flex;align-items: center;justify-content: center">
                        <span style="font-size: 30px;" v-html="valeur_indicateur"></span>
                    </a>
                    <span v-else style="font-size: 30px;" v-html="valeur_indicateur"></span>
                    <br/>
                    <template v-if="objectif">
                        <span :class="'badge badge-' + (objectif_atteint == 1 ? 'success' : 'danger')">@traduction('rapport.divers.obj') @{{ objectif }}</span>
                    </template>
                </div>
            </div>
        </div>`,
        props: {
            id_rapport: {
                type: String,
                default: '',
            },
            titre_rapport: {
                type: String,
                default: '',
            },
            icone_dans_rapport: {
                type: String,
                default: '',
            },
            valeur_indicateur: {
                type: Number | String,
                default: null,
            },
            objectif_atteint: {
                type: Number,
                default: '',
            },
            objectif: {
                type: Number|Boolean,
                default: '',
            },
            unite: {
                type: String,
                default: '',
            },
            lien: {
                type: String,
                default: '',
            },
            actualisation_rapport:{
                type: Boolean,
                default: false,
            }
        }
    });
</script>
