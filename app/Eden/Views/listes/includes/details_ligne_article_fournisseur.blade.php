<table class="table table-bordered" style="">
    <thead>
    <tr>
        <th>@traduction('champs_libres.condition_commerciale.prix_achat.nom')</th>
        <th>@traduction('champs_libres.condition_commerciale.palier_quantite.nom')</th>
        <th>@traduction('champs_libres.condition_commerciale.catalogue_tarif_id.nom')</th>
        <th>@traduction('champs_libres.condition_commerciale.modifie_le.nom')</th>
    </tr>
    </thead>
    <tbody>

    @foreach($conditions_commerciales as $condition_commerciale)
        <tr>
            <td>
                {!! $condition_commerciale->prix_achat !!}
            </td>
            <td>
                {!! $condition_commerciale->palier_quantite !!}
            </td>
            <td>
                {!! $condition_commerciale->catalogue_tarif_id !!}
            </td>
            <td>
                {!! $condition_commerciale->modifie_le !!}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>