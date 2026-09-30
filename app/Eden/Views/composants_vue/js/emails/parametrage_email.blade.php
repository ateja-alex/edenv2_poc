<script>
    const parametrage_email = Vue.component('parametrage-email', {
        template: `
            <span>
                <div class="row mb-3" v-if="type_element == null">
                    <div class="col-md-6">
                        <label>Type d'élément :</label>
                        <select-table-libre 
                            :tables_libres="tables_libres" 
                            :type_element="type_element_email" nom="type_element"
                            @changement_select_table_libre="type_element_email = ($event == null ? null : $event.type_element)">
                        </select-table-libre>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 css_module_onglets" v-if="id_type_element !== null">
                        <ul class="nav nav-tabs liste_onglets" style="position: relative;border-bottom: 0px solid #dedede;background: #dedede;padding: 10px; padding-left: 0;gap: 21px 0px;">
                            <li class="css_pointer" v-for="onglet in onglets">
                                <a @click="onglet_actif = onglet" :class="'css_background_couleur_primaire_active '+(onglet_actif == onglet ?'active' : '')" style="margin-left: 1px;">
                                    <span v-html="$root.traduction('composant.emails.parametrage_email.onglets.'+onglet)"></span>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div v-show="onglet_actif == 'parametrage_globale'">
                                <component :is="'liste-libre-'+ids_listes.parametrage_destinataire_email"
                                    :filtres_pour_fiche="{'type_element_id': id_type_element}"
                                    :modele_par_defaut="Object.assign(modele_par_defaut_destinataire_email, {type_element_id: id_type_element})"
                                    :mode_parametrage="1"
                                    ref="listes" >
                                </component>
                                <component :is="'liste-libre-'+ids_listes.parametrage_piece_jointe_email"
                                    :filtres_pour_fiche="{'type_element_id': id_type_element}"
                                    :modele_par_defaut="Object.assign(modele_par_defaut_piece_jointe_email, {type_element_id: id_type_element})"
                                    :mode_parametrage="1"
                                    ref="listes" >
                                </component>
                                <component :is="'liste-libre-'+ids_listes.parametrage_balise_publipostage"
                                    :filtres_pour_fiche="{'type_element_id': id_type_element}"
                                    :modele_par_defaut="Object.assign(modele_par_defaut_balise_email, {type_element_id: id_type_element})"
                                    :mode_parametrage="1"
                                    ref="listes" >
                                </component>
                            </div>
                            <div v-show="onglet_actif == 'modele_email'">
                                <component :is="'liste-libre-'+ids_listes.modele_email"
                                    :filtres_pour_fiche="{'type_element_id': id_type_element}"
                                    :modele_par_defaut="Object.assign(modele_par_defaut_modele_email, {type_element_id: id_type_element})"
                                    :mode_parametrage="1"
                                     >
                                </component>
                            </div>
                        </div>
                    </div>
                </div>
            </span>
        `,
        props: {
            type_element : {
                type : String,
                default: null
            },
            ids_listes : {
                type : Object,
                default: function(){
                    return {
                        modele_email : null,
                        parametrage_destinataire_email : null,
                        parametrage_piece_jointe_email : null,
                        parametrage_balise_publipostage : null
                    }
                }
            },
        },
        data:function(){
            return {
                tables_libres : {!! \App\Eden\Models\Table_libre::where(function($where){
                    $where->whereNull('table_systeme')->orWhere('table_systeme',0);
                })->where('envoyer_email',1)->get() !!},
                type_element_email : null,
                modele_par_defaut_modele_email : {},
                modele_par_defaut_destinataire_email : {},
                modele_par_defaut_piece_jointe_email : {},
                modele_par_defaut_balise_email : {},
                onglet_actif:'parametrage_globale',
                onglets:['parametrage_globale','modele_email']
            }
        },
        computed: {
            id_type_element(){
                return this.tables_libres.find(t => t.type_element == this.type_element_email)?.id ?? null;
            },
        },
        mounted: async function(){
            if(this.type_element !== null)
                this.type_element_email = this.type_element;

            this.modele_par_defaut_modele_email = await this.$root.modele_par_defaut('modele_email');
            this.modele_par_defaut_destinataire_email = await this.$root.modele_par_defaut('parametrage_destinataire_email');
            this.modele_par_defaut_piece_jointe_email = await this.$root.modele_par_defaut('parametrage_piece_jointe_email');
            this.modele_par_defaut_balise_email = await this.$root.modele_par_defaut('parametrage_balise_publipostage');
        },
        directives: {
            
        }
    });
</script>