<label class="badge_planning" :class="{'badge_planning_selected': selection_tache && selection_tache_ids.includes(tache.id)}" :for="'bulle_tache_'+tache.id"  :data-tache-id="tache.id" :data-tache-journee-entiere="tache.journee_entiere ? 1 : 0"
      :style="tache.style"
      @mouseenter="tache_affichage_options = tache.id;"
      @mouseleave="tache_affichage_options = null;"
      v-for="tache in taches_par_utilisateur[utilisateur.id][date]" v-if="tache.type == 'tache'"
      @dblclick.stop="!selection_tache && (tache.prive != 1 || (tache.prive == 1 && utilisateur.id == $root.moi.id)) ? afficher_tache(tache.id) : null"
      v-tooltip_tache="tache" :key="tache.id">
    <span class="options_tache_planning">
        <span class="bulle_option_planning suppression" @click="supprimer_tache(tache.id)" v-if="tache_affichage_options == tache.id && !selection_tache && !lecture_seule && (tache.prive != 1 || tache.prive == 1 && utilisateur.id == $root.moi.id)">
            <i class="fas fa-trash"></i>
        </span>
        <span class="bulle_option_planning" v-if="tache.prive" :title="$root.traduction('composant.affichage_calendrier.privee')">
            <i class="fas fa-lock"></i>
        </span>
        <input v-if="selection_tache" :id="'bulle_tache_'+tache.id" class="bulle_option_planning" @click.stop type="checkbox" v-model="selection_tache_ids" :value="tache.id" />
    </span>
    <span class="titre_planning" v-html="tache.label"></span>
    <span class="indication_numero_jours" v-if="tache.numero_de_jours != null">
        @{{ tache.numero_de_jours }}
    </span>

</label>
