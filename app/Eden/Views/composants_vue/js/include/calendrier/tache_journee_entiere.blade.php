<div @dblclick="ajouter_tache(null,null,tache)" :data-tache-nombre-demies-heures="tache.nombre_demies_heures"
     :class="classes_tache(tache)" :data-tache-id="tache.id" :data-tache-date-debut="tache.date_de_debut"
     :data-tache-date-fin="tache.date_de_fin" data-tache-journee-entiere="1" v-tooltip_tache="tache"
     :style="'position:relative;padding: 5px;margin: 5px;'+tache.style" :key="tache.id">
    <span>
        <i v-show="tache.urgent == 1" class="fas fa-exclamation-triangle" :title="$root.traduction('composant.affichage_calendrier.urgent')" data-toggle="tooltip"></i>
        <b v-html="tache.label"></b><br>
        @{{ tache.affectation | affiche_utilisateur }}
        <i v-if="tache.commentaire != null && tache.commentaire != ''" :title="tache.commentaire_title" data-toggle="tooltip" class="far fa-envelope css_btn_action_theme "></i>
        <span style="font-style: italic;">@{{ tache.affichage_nom_tache }}</span>
    </span>
    <div style="display: flex; position: absolute; bottom: 5px; right: 5px; gap: 2px;">
        <i v-show="tache.prive != null" class="fas fa-lock" :title="$root.traduction('composant.affichage_calendrier.privee')" data-toggle="tooltip"></i>
        <i v-show="tache.parent_id != null" class="fas fa-sync" :title="$root.traduction('composant.affichage_calendrier.recurrence')" data-toggle="tooltip"></i>
    </div>
</div>