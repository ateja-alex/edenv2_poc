<div :class="liste.modele_liste_libre.formulaire_modale ? 'modal-content' : 'card'">
    <div :class="liste.modele_liste_libre.formulaire_modale ? 'modal-header' : 'card-header'" style="align-items: center">
        <h4 :class="liste.modele_liste_libre.formulaire_modale ? 'modal-title' : ''" >
            <span v-if="!liste.modele_liste_libre.id_rapport" v-html="$root.traduction('tables_libres.'+liste.type_element+'.nom_table')"></span>
            <span v-else v-html="$root.traduction('rapport.'+liste.modele_liste_libre.id_rapport+'.titre')"></span>
            <span v-show="liste.duplication_en_cours == true"> - <span style="background: yellow; padding: 0px 5px;">@traduction('interface.listes.duplication')</span></span>
        </h4>
        <template v-if="mode_parametrage == 1">
            <a :href="'/eden/parametrage/formulaire/'+(liste.modele_liste_libre.formulaire_libre || liste.type_element_options || liste.type_element)" class="css_bouton_modifier_liste_primaire ml-3">
                <i class="fas fa-cog"></i> Paramétrer Formulaire
            </a>
        </template>
        @include('eden::listes.includes.acces_rapide.contact')


        <div class="save-button-container">
            <span @click="enregistrer_dans_liste" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" :title="$root.traduction('interface.enregistrer')">
                <i class="css_action_icon secondaire fa fa-fw fa-save"></i>
            </span>
            <span class="css_ajouter_element bouton_dropdown_enregistrer" style="padding: 0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="css_action_icon secondaire fas fa-angle-down"></i>
            </span>
            <div class="dropdown-menu">
                <span class="dropdown-item" @click="enregistrer_dans_liste({}, 1)">@traduction('interface.listes.enregistrer_et_nouveau')</span>
                <span class="dropdown-item" @click="enregistrer_dans_liste({}, 2)">@traduction('interface.listes.enregistrer_et_dupliquer')</span>
            </div>
            <button type="button" class="close" @click="retour_a_la_liste()" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
    <div :class="liste.modele_liste_libre.formulaire_modale ? 'modal-body' : 'card-body'">
        <formulaire ref="formulaire_liste" :nom_formulaire="liste.modele_liste_libre.formulaire_libre || liste.type_element_options || liste.type_element"></formulaire>
        <div class="row" v-if="liste.duplication_en_cours == true && id_element != null && Object.values(liste.elements_a_copier).length > 0">
            <div class="col-sm-2">
                @traduction('interface.listes.elements_a_copier')
            </div>
            <div class="col-md-10" >
                <template v-for="element_a_copier in liste.elements_a_copier">
                    <input :id="'element_a_copier_'+element_a_copier.type_element" type="checkbox" v-model="liste_elements_a_dupliquer" :value="element_a_copier.type_element">
                    <label :for="'element_a_copier_'+element_a_copier.type_element" v-html="$root.traduction('tables_libres.'+element_a_copier.type_element+'.nom_table')"></label>
                </template>
            </div>
        </div>
    </div>
    <div :class="liste.modele_liste_libre.formulaire_modale ? 'modal-footer' : 'card-footer'" style="text-align: right">

        @if(View::hasSection('options_modale_edition'))
            @yield('options_modale_edition')
        @else
        <div style="display: flex;gap:5px;justify-content: flex-end">

            <a :href="'/eden/fiche/'+liste.type_element+'/'+liste.element_id_modification" class="btn btn-secondary css_btn_responsive" v-if="liste.fiche == 1 && liste.element_id_modification != null && liste.element_id_modification != ''">
                @traduction('interface.listes.afficher')
            </a>
            <button type="button" class="btn btn-secondary css_btn_responsive" @click="retour_a_la_liste();">
                @traduction('interface.listes.fermer')
            </button>
            <button type="button" class="btn btn-danger css_btn_responsive" v-if="(ligne_modification == null || ligne_modification.droits_suppression) && liste.element_id_modification != null && liste.element_id_modification != ''" @click="supprimer_dans_liste(liste.element_id_modification); retour_a_la_liste();">
                @traduction('interface.listes.supprimer')
            </button>

            <div class="conteneur_boutons_enregistrement" v-if="ligne_modification == null || ligne_modification.droits_modification">
                <button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_dans_liste()">
                    @traduction('interface.listes.enregistrer')
                </button>
                <span class="btn btn-primary css_btn_responsive bouton_dropdown_enregistrer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-angle-down"></i>
                </span>
                <div class="dropdown-menu">
                    <span class="dropdown-item" @click="enregistrer_dans_liste({}, 1)">@traduction('interface.listes.enregistrer_et_nouveau')</span>
                    <span class="dropdown-item" @click="enregistrer_dans_liste({}, 2)">@traduction('interface.listes.enregistrer_et_dupliquer')</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
