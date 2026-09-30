<script>
const modification_en_masse = Vue.component('modification-en-masse', {
    template: `<transition name="modal">
                    <div class="modal-mask" v-if="ouvert">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">@traduction('interface.listes.modifier')</h5>
                                    <button type="button" class="close" @click="fermer()" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body css_form">
                                    <form ref="form_modification" class="css_form css_modif_en_masse_form">
                                        <div v-for="champ in champs_modifications"
                                             class="row css_modif_en_masse_ligne"
                                             :class="{ 'css_modif_en_masse_ligne_active': modif_en_masse.includes(champ.nom_sql) }">
                                            <div class="col-md-4 css_modif_en_masse_col_label">
                                                <input type="checkbox"
                                                       :id="'modif_en_masse_actif_'+champ.nom_sql"
                                                       :value="champ.nom_sql"
                                                       v-model="modif_en_masse"
                                                       class="css_modif_en_masse_checkbox">
                                                <label class="css_modif_en_masse_label"
                                                       :class="{ 'css_modif_en_masse_label_actif': modif_en_masse.includes(champ.nom_sql) }"
                                                       :for="'modif_en_masse_actif_'+champ.nom_sql"
                                                       v-html="$root.traduction(champ.index_traduction+'.nom')">
                                                </label>
                                            </div>
                                            <div class="col-md-8">
                                                <div v-if="modif_en_masse.includes(champ.nom_sql)" class="css_modif_en_masse_saisie">
                                                    <div v-if="![2,3,4,5].includes(champ.type) ||
                                                            ([4,5].includes(champ.type) && !Object.values(delta_dates[champ.nom_sql]).some(valeur => valeur != 0 && valeur != ''))
                                                            || ([2,3].includes(champ.type) && (delta_nombres[champ.nom_sql] == 0 || delta_nombres[champ.nom_sql] == ''))"
                                                         class="css_modif_en_masse_bloc_composant">
                                                        <span v-if="[2,3,4,5].includes(champ.type)" class="css_modif_en_masse_label_section">
                                                            @traduction('composant.modification_en_masse.nouvelle_valeur')
                                                        </span>
                                                        <component :is="champ.composant"></component>
                                                        <div v-if="champ.type == 10" class="css_modif_en_masse_ajout_multiple">
                                                            <input type="checkbox" v-model="ajouts_multiples" :value="champ.nom_sql" checked>
                                                            @{{ $root.traduction('composant.liste_libre.action.modifier_en_masse.ajouter_valeurs_champ_multiple') }}
                                                        </div>
                                                    </div>
                                                    <template v-if="[2,3,4,5].includes(champ.type)">
                                                        <div class="css_modif_en_masse_separateur_ou"
                                                             v-if="(element_modification_en_masse[champ.nom_sql] == null || element_modification_en_masse[champ.nom_sql] == '')
                                                                && (![4,5].includes(champ.type) || !Object.values(delta_dates[champ.nom_sql]).some(valeur => valeur != 0 && valeur != ''))
                                                                && (![2,3].includes(champ.type) || (delta_nombres[champ.nom_sql] == 0 || delta_nombres[champ.nom_sql] == ''))">
                                                            <span>
                                                                @traduction('composant.modification_en_masse.ou')
                                                            </span>
                                                        </div>
                                                        <div v-if="[4,5].includes(champ.type) && (element_modification_en_masse[champ.nom_sql] == null || element_modification_en_masse[champ.nom_sql] == '')"
                                                             class="css_modif_en_masse_bloc_delta">
                                                            <span class="css_modif_en_masse_label_section">
                                                                @traduction('composant.modification_en_masse.decalage')
                                                            </span>
                                                            <div class="css_modif_en_masse_delta_date">
                                                                <div v-for="id_delta in Object.keys(delta_dates[champ.nom_sql])"
                                                                     v-if="!['heures','minutes'].includes(id_delta) || champ.type == 5"
                                                                     class="css_modif_en_masse_delta_item">
                                                                    <span class="css_modif_en_masse_delta_label"
                                                                          v-html="$root.traduction('composant.modification_en_masse.delta.'+id_delta)"></span>
                                                                    <input type="number"
                                                                           v-model.number="delta_dates[champ.nom_sql][id_delta]"
                                                                           class="css_modif_en_masse_delta_input">
                                                                </div>
                                                                <input type="hidden"
                                                                       v-if="Object.values(delta_dates[champ.nom_sql]).some(valeur => valeur != 0 && valeur != '')"
                                                                       :name="champ.nom_sql"
                                                                       :value="JSON.stringify(delta_dates[champ.nom_sql])">
                                                            </div>
                                                        </div>
                                                        <div v-if="[2,3].includes(champ.type) && (element_modification_en_masse[champ.nom_sql] == null || element_modification_en_masse[champ.nom_sql] == '')"
                                                             class="css_modif_en_masse_bloc_delta">
                                                            <span class="css_modif_en_masse_label_section">
                                                                @traduction('composant.modification_en_masse.ajustement')
                                                            </span>
                                                            <div class="css_modif_en_masse_delta_nombre">
                                                                <input type="number"
                                                                       v-model.number="delta_nombres[champ.nom_sql]"
                                                                       class="css_modif_en_masse_delta_input_nombre"
                                                                       :placeholder="$root.traduction('composant.modification_en_masse.ajustement.placeholder')">
                                                                <span class="css_modif_en_masse_hint">
                                                                    @traduction('composant.modification_en_masse.ajustement_detail')
                                                                </span>
                                                            </div>
                                                            <input type="hidden"
                                                                   v-if="delta_nombres[champ.nom_sql] != 0 && delta_nombres[champ.nom_sql] != ''"
                                                                   :name="champ.nom_sql"
                                                                   :value="JSON.stringify({delta: delta_nombres[champ.nom_sql]})">
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" @click="fermer()">@traduction('interface.listes.fermer')</button>
                                    <button type="button" class="btn btn-primary" :class="ids_elements.length == 0 ? 'disabled' : ''" @click="modifier_elements_selectionnes()">
                                        @traduction('interface.listes.modifier.action_elements_selectionnes')
                                        (@{{ ids_elements.length }})
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>`,


    props: {
        ids_elements: {
            type: Array,
            default: function() { return []; }
        },
        type_element: {
            type: String,
            default: ''
        }
    },

    data: function() {
        return {
            ouvert: false,
            champs_modifications: [],
            element_modification_en_masse: {},
            modif_en_masse: [],
            ajouts_multiples: [],
            delta_dates: {},
            delta_nombres: {},
        }
    },

    methods: {

        modifier_elements_selectionnes: function() {

            var ids = this.ids_elements;

            if(ids.length == 0)
                return;

            this.eden_modifier_en_masse({ ids_elements: ids, type_element: this.type_element });
        },

        eden_modifier_en_masse: function(parametres) {

            var composant = this;

            var data = {};

            $.each($(this.$refs.form_modification).serializeArray(), (_, nom_valeur) => {
                var nom = nom_valeur.name.replace('[]', '');
                var valeur = nom_valeur.value;
                var champ_libre = null;

                for(champ of composant.champs_modifications) {
                    if(champ.nom_sql == nom)
                        champ_libre = champ;
                }

                if(champ_libre != null) {
                    if([10, 11, 12].includes(champ_libre.type)) {
                        if(data[nom] == undefined)
                            data[nom] = [];
                        data[nom].push(valeur);
                    } else
                        data[nom] = valeur;
                }
            });

            parametres.ajouts_multiples = this.ajouts_multiples;

            if(Object.keys(data).length == 0)
                return;

            loading(true);

            $.post({
                url: 'eden/elements/modifier_en_masse',
                method: 'post',
                dataType: 'json',
                data: { parametres: parametres, form: data },
            }).done(async (retour) => {

                if(retour.success == true) {
                    this.fermer();
                    this.$emit('modification_terminee');
                } else {
                    retour.message = retour.message.replace('<br>', '\n');
                    await alerte_eden(composant.$root.traduction('interface.listes.des_erreurs_sont_survenues') + "\n\n" + composant.$root.traduction('interface.listes.lignes_modifiees') + retour.lignes_modifiees + "\n" + retour.message);
                    loading(false);
                    return;
                }

                loading(false);
            });
        },

        ouvrir: async function() {

            await this.initialiser();
            this.ouvert = true;
        },

        fermer: function() {

            this.ouvert = false;
        },

        initialiser: async function() {

            this.element_modification_en_masse = {};
            this.modif_en_masse = [];
            this.delta_dates = {};
            this.delta_nombres = {};

            var composant = this;

            var donnees = await $.post({
                url: 'eden/champs/modification_en_masse/' + this.type_element,
                dataType: 'json',
            });

            champs_modifications = donnees.champs_modifications;

            this.champs_modifications = champs_modifications;

            for(champ of champs_modifications) {
                champ.composant = {
                    template: champ.champ_creation,
                    name: 'champ_modification_' + champ.nom_sql,
                    methods: this.$options.methods,
                    data: function() {
                        var data = {};
                        data[champ.type_element] = composant.element_modification_en_masse;
                        return data;
                    },
                };

                this.$set(this.element_modification_en_masse,champ.nom_sql,champ.type == 10 ? [] : null);

                if(champ.type == 10)
                    this.ajouts_multiples.push(champ.nom_sql);

                if([4, 5].includes(champ.type))
                    this.$set(this.delta_dates, champ.nom_sql, { annees: 0, mois: 0, jours: 0, heures: 0, minutes: 0 });

                if([2, 3].includes(champ.type))
                    this.$set(this.delta_nombres, champ.nom_sql, 0);
            }
        },

    },
});
</script>
