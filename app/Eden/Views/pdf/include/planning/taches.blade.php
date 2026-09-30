@foreach($taches as $utilisateur => $taches_par_date)
    <tr>
        <td class="colonne_utilisateur">
            {{$utilisateurs[$utilisateur]->prenom .' '.$utilisateurs[$utilisateur]->nom}}
        </td>
        @foreach($taches_par_date as $taches)
            <td>
                @foreach($taches as $tache)
                    <div class="bloc_tache" style="margin: 2px;">
                        <b>{!! $tache['label'] !!}</b>
                    </div>
                @endforeach
            </td>
        @endforeach
    </tr>
@endforeach