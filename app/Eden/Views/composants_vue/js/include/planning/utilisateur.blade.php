<div class="colonne_utilisateur_contenu">
    @if(fonctionnalite('planning_afficher_avatar_utilisateur'))
        <span @click="affichage_formulaire_utilisateur(utilisateur)" class="avatar-utilisateur">
            <img :src="utilisateur.avatar ? 'storage/' + utilisateur.avatar : 'eden/images/no_avatar.jpg'">
        </span>
        <span>@{{ utilisateur.prenom }} @{{ utilisateur.nom }}</span>
    @else
        <span class="css_lien" @click="affichage_formulaire_utilisateur(utilisateur)">@{{ utilisateur.prenom }} @{{ utilisateur.nom }}</span>
    @endif
    <span  v-if="!$root.intranet">
        <a target="_blank" :href="lien_calendrier(utilisateur.id)"
        >
            <i class="far fa-calendar-alt"></i>
        </a>
    </span>
</div>