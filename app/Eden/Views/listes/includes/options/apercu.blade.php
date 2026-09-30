<span v-if="$root.intranet && apercu_liste_intranet()" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="far fa-eye"
          @click="$root.charger_formulaire(type_element,ligne.element.id,id_liste)"
          data-toggle="tooltip"
          :title="$root.traduction('interface.listes.apercu_rapide')"
          :id_element="ligne.element.id">
    </span>
</span>
<span v-if="!($root.intranet)" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="far fa-eye"
          @click="modifier_dans_liste(ligne.element.id)"
          data-toggle="tooltip"
          :title="$root.traduction('interface.listes.apercu_rapide')"
          :id_element="ligne.element.id">
    </span>
</span>

@push('donnees_pour_vuejs_methods')
    apercu_liste_intranet(){
        const module_liste = this.$root.module_avec_liste_id(this.id_liste);

        return module_liste.formulaire_liste != null;
    },

@endpush