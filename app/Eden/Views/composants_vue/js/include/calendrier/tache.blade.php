<div @dblclick="ajouter_tache(null,null,tache)" v-tooltip_tache="tache" :class="classes_tache(tache)" :data-tache-nombre-demies-heures="tache.nombre_demies_heures"
      :data-tache-id="tache.id" :data-tache-date-debut="tache.date_de_debut" :data-tache-date-fin="tache.date_de_fin" :key="tache.id"
      :style="'position:absolute;height:calc('+ 100 * tache.nombre_demies_heures + '% + ' +tache.nombre_demies_heures+ 'px);min-height:calc('+ 100 * tache.nombre_demies_heures + '% + ' +tache.nombre_demies_heures+ 'px); width:'+ 100 * agenda[date.format_us].pourcentage_taille_evenement /100 +'%; margin-left:'+tache.ordre*  100   * agenda[date.format_us].pourcentage_taille_evenement /100 +'%;margin-top:'+(hauteur_ligne_calendrier / 2 - hauteur_ligne_calendrier * (100 - tache.pourcentage_avant_debut) / 100)+'px;'+tache.style">
    <div v-if="peut_redimensionner_tache(tache)" class="poignee_redimensionnement_tache haut" @mousedown.stop.prevent="demarrer_redimensionnement($event, tache, 'debut')"></div>
        <div class="contenu_tache">
            <span class="titre_tache" :style="'top:'+($refs.header_sticky ? $refs.header_sticky.clientHeight + 5 : 0)+'px'">
                <i v-show="tache.urgent == 1" class="fas fa-exclamation-triangle" :title="$root.traduction('composant.affichage_calendrier.urgent')" data-toggle="tooltip"></i>
                <b v-html="tache.label"></b><br>
                @{{ tache.affectation | affiche_utilisateur }}
                <i v-if="tache.commentaire != null && tache.commentaire != ''" :title="tache.commentaire_title" data-toggle="tooltip" class="far fa-envelope css_btn_action_theme"></i>
                <i v-if="tache.terminee == 1" :title="$root.traduction('composant.affichage_calendrier.terminee')" data-toggle="tooltip" class="fas fa-check "></i>
                <span style="font-style: italic;">@{{ tache.affichage_nom_tache }}</span>
            </span>
            <span style="position: absolute;bottom: 5px;right: 5px;" v-if="tache.prive" :title="$root.traduction('composant.affichage_calendrier.privee')">
                <i class="fas fa-lock"></i>
            </span>
            <span style="position: absolute;top: 5px;right: 5px;" v-if="tache.parent_id != null" :title="$root.traduction('composant.affichage_calendrier.recurrence')">
                <i class="fas fa-sync"></i>
            </span>
            <span style="position: absolute;top: 5px;right: 5px;" v-if="tache.parent_id != null && tache.exception_recurrence" :title="$root.traduction('composant.affichage_calendrier.recurrence')">
                <i class="fas fa-slash"></i>
            </span>
        </div>
    <div v-if="peut_redimensionner_tache(tache)" class="poignee_redimensionnement_tache bas" @mousedown.stop.prevent="demarrer_redimensionnement($event, tache, 'fin')"></div>
</div>
