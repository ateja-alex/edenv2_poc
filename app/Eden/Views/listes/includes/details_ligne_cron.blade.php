<table class="table table-bordered" style="">
    <thead>
    <tr>
        <th>@traduction('champs_libres.cron_parametres.id_utilisateur.nom')</th>
        <th>@traduction('champs_libres.cron_parametres.id_entite.nom')</th>
        <th>@traduction('champs_libres.cron_parametres.nom.nom')</th>
        <th>@traduction('champs_libres.cron_parametres.valeur.nom')</th>
        <th>@traduction('champs_libres.cron_parametres.date_creation.nom')</th>

    </tr>
    </thead>
    <tbody>

    @foreach($parametres as $parametre)
        <tr>
            <td>
                {!! $parametre->id_utilisateur !!}
            </td>
            <td>
                {!! $parametre->id_entite !!}
            </td>
            <td>
                {!! $parametre->nom !!}
            </td>
            <td>
                <div class="zoom_ligne_texte_diminue">
                    {!! $parametre->valeur !!}
                </div>
            </td>
            <td>
                {!! $parametre->date_creation !!}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>