<thead>
    <tr>
        <th>
            <div>
                <input type="text" v-model="{{$lignes}}[index][index_cell].label_taux"
                       placeholder="{!! traduction('liste.code_tva.colonne.taux.nom') !!}">
                <a class="fab fa-css3-alt" title=".recap_tva .titre" data-toggle="tooltip"></a>
            </div>
        </th>
        <th>
            <div>
                <input type="text" v-model="{{$lignes}}[index][index_cell].label_ht"
                       placeholder="{!! traduction('liste.code_ht.colonne.ht.nom') !!}">
                <a class="fab fa-css3-alt" title=".recap_tva .titre" data-toggle="tooltip"></a>
            </div>
        </th>
        <th>
            <div>
                <input type="text" v-model="{{$lignes}}[index][index_cell].label_tva"
                       placeholder="{!! traduction('liste.code_tva.colonne.tva.nom') !!}">
                <a class="fab fa-css3-alt" title=".recap_tva .titre" data-toggle="tooltip"></a>
            </div>
        </th>
    </tr>
</thead>

<tbody>
    <tr>
        <td>
            10%
            <a class="fab fa-css3-alt" title=".recap_tva .valeur_taux" data-toggle="tooltip"></a>
        </td>
        <td >
            100
            <a class="fab fa-css3-alt" title=".recap_tva .montant_ht" data-toggle="tooltip"></a>
        </td>
        <td >
            10
            <a class="fab fa-css3-alt" title=".recap_tva .montant_tva" data-toggle="tooltip"></a>
        </td>
    </tr>

    <tr>
        <td>
            20%
            <a class="fab fa-css3-alt" title=".recap_tva .valeur_taux" data-toggle="tooltip"></a>
        </td>
        <td>
            200
            <a class="fab fa-css3-alt" title=".recap_tva .montant_ht" data-toggle="tooltip"></a>
        </td>
        <td>
            40
            <a class="fab fa-css3-alt" title=".recap_tva .montant_tva" data-toggle="tooltip"></a>
        </td>
    </tr>
</tbody>
