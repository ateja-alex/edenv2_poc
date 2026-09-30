<span :class="'badge_planning conge ' + classe_conge(tache)" v-for="tache in taches_par_utilisateur[utilisateur.id][date]" 
    v-if="tache.type == 'conge'" @click.stop v-tooltip_tache="tache" :key="tache.id">
    <span class="titre_planning">
        @{{ tache.label }}
    </span>
    <span class="indication_numero_jours" v-if="tache.numero_de_jours != null">
        @{{ tache.numero_de_jours }}
    </span>
</span>