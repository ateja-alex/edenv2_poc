<h3>{!! traduction('pdf.tache_rdv.titre', $langue) !!}</h3>
<table class="css_tableau_ca_indicatifs">
    <tbody>
    <tr>
        <td>
            <div class="css_chiffre_indicateur">
                {{ $donnees['moyenne'] }}
            </div>
            <div class="css_separateur_indicateur"></div>
            <span>{!! traduction('pdf.tache_rdv.moyenne', $langue) !!}</span>
        </td>
    </tr>
    </tbody>
</table>
@if($donnees['taches']->isNotEmpty())

    <table class="css_tableau_donnees_fiche_pdf">
        <tbody>
            <tr>
                <td><b>{{ management('tache')->champ('titre')->modele->nom }}</b></td>
                <td><b>{{ management('tache')->champ('date_de_debut')->modele->nom }}</b></td>
                <td><b>{{ management('tache')->champ('date_de_fin')->modele->nom }}</b></td>
                <td><b>{{ management('tache')->champ('commentaire')->modele->nom }}</b></td>

            </tr>
            @foreach($donnees['taches'] as $tache)
                <tr>
                    <td>{{ $tache->titre }}</td>
                    <td>{{ $tache->date_de_debut }}</td>
                    <td>{{ $tache->date_de_fin }}</td>
                    <td>{{ $tache->commentaire }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <h4>{!! traduction('pdf.tache.pas_de_donnees', $langue) !!}</h4>

@endif
