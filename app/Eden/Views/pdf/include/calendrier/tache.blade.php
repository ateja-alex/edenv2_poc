<div class="bloc_tache_calendrier affichage_semaine"
     style="height:{{ 19.5 * $tache->nombre_demies_heures }}px;width:{{ $agenda[$date['format_us']]['pourcentage_taille_evenement'] - $operande_width}}%;margin-left: {{ $tache->ordre * $agenda[$date['format_us']]['pourcentage_taille_evenement'] * 1.64 - 1 }}px;margin-top:{{ $tache->pourcentage_avant_debut == 0 ? -10 : (100 - $tache->pourcentage_avant_debut) / 100 }}px;">
    <span class="titre_tache">
        @if(isset($tache->client_id_formate))
            <span>{!! $tache->client_id_formate !!}</span><br>
        @endif
        <b>{!! $tache->titre !!}</b><br>
        <span>{{ $utilisateur->prenom . ' ' . $utilisateur->nom }}</span>
    </span>
</div>