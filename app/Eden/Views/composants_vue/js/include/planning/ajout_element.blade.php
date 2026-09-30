<span class="badge_planning ajout_tache" :style="tache.style" v-if="!selection_tache"
    @click.stop="creer_tache(utilisateur.id, date @if(isset($creation_demi_journee)) , '{{ $creation_demi_journee }}' @endif)">
    <span class="titre_planning">
        <i class="fa fa-plus"></i>
        @traduction('composant.planning.ajouter_tache')
    </span>
</span>