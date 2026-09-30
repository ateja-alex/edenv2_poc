<div class="details_lignes_docusign_enveloppe">
    <div class="details_lignes_docusign_enveloppe_bloc">
        <h6>{{ucfirst(traduction('tables_libres.docusign_signataire.element_pluriel'))}}</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    @foreach($colonnes_signataires as $colonne)
                        <th>{{traduction('interface.docusign.colonnes_signataires.'.$colonne)}}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($signataires as $parametre)
                <tr>
                    @foreach($colonnes_signataires as $colonne)
                        <td>{{$parametre[$colonne]}}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="details_lignes_docusign_enveloppe_bloc">
        <h6>{{ucfirst(traduction('tables_libres.docusign_document.element_pluriel'))}}</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    @foreach($colonnes_documents as $colonne)
                        <th>{{traduction('interface.docusign.colonnes_documents.'.$colonne)}}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($documents as $parametre)
                <tr>
                    @foreach($colonnes_documents as $colonne)
                        <td>
                            @if(is_array($parametre[$colonne]))
                                <a target="_blank" href="{{$parametre[$colonne]['chemin']}}">{{$parametre[$colonne]['nom']}}</a>
                            @else
                                {{$parametre[$colonne]}}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>