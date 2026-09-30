@extends('eden::composants_vue.js.liste_libre')

@section('options_modale_edition')
    <div style="display: flex;gap:5px;justify-content: flex-end">

        <button type="button" class="btn btn-secondary css_btn_responsive" @click="retour_a_la_liste();">
            @traduction('interface.listes.fermer')
        </button>

        <button type="button" class="btn btn-danger css_btn_responsive" v-if="liste.element_id_modification != null && liste.element_id_modification != ''" @click="supprimer_dans_liste(liste.element_id_modification); retour_a_la_liste();">
            @traduction('interface.listes.supprimer')
        </button>

        <!-- Boutons de modification -->
        <div class="dropdown" v-if="ligne_modification != null && ligne_modification.element.parent_id > 0 && !ligne_modification.exception_recurrence">
            <div class="btn btn-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@traduction('interface.modales.enregistrer')</div>
            <div class="dropdown-menu">
                <div style="display:flex;flex-direction: column;gap:5px">
                    <span class="dropdown-item" @click="enregistrer_dans_liste()">@traduction('composant.affichage_calendrier.cet_evenement')</span>
                    <span class="dropdown-item" @click="enregistrer_dans_liste({modifier_recurrence : 1})" v-if="$refs.formulaire != undefined && $refs.formulaire.$refs.formulaire.participants_modifies?.() !== true">@traduction('composant.affichage_calendrier.evenements_suivants')</span>
                    <span class="dropdown-item" @click="enregistrer_dans_liste({modifier_recurrence : 2})">@traduction('composant.affichage_calendrier.toute_la_serie')</span>
                </div>
            </div>
        </div>
        <div v-else class="conteneur_boutons_enregistrement">
            <button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_dans_liste()">
                @traduction('interface.listes.enregistrer')
            </button>
            <template v-if="ligne_modification != null && !ligne_modification.exception_recurrence">
                <span class="btn btn-primary css_btn_responsive bouton_dropdown_enregistrer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-angle-down"></i>
                </span>
                <div class="dropdown-menu">
                    <span class="dropdown-item" @click="enregistrer_dans_liste({}, 1)">@traduction('interface.listes.enregistrer_et_nouveau')</span>
                    <span class="dropdown-item" @click="enregistrer_dans_liste({}, 2)">@traduction('interface.listes.enregistrer_et_dupliquer')</span>
                </div>
            </template>
        </div>
    </div>
@endsection