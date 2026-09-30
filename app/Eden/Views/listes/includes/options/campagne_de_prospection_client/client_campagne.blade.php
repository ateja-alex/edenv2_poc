<span v-if="ligne.element.client_id > 0 && ligne.element.campagne_de_prospection_id > 0"
      class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <a :href="'eden/campagne_de_prospection/'+ligne.element.campagne_de_prospection_id+'/client/'+ligne.element.client_id+'/lancer?id_liste='+id_liste+'&champ_a_recuperer=client_id'">
        <span class="fas fa-play js_liste_lien_element"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.voir_la_fiche')">
        </span>
    </a>
</span>