<p style="text-align: right;cursor: pointer;" class="js_fermer_detail_ligne"><i class="fa fa-times" aria-hidden="true"></i></p>
    <table class="table table-bordered" width="100%" cellspacing="0" colspan="8">
        <thead>
            <tr>
                <th>#</th>
                <th>@traduction('interface.listes.details_ligne_budget_insight.client')</th>
                <th>@traduction('interface.listes.details_ligne_budget_insight.montant')</th>
                <th>@traduction('interface.listes.details_ligne_budget_insight.mode_de_paiement')</th>
                <th>@traduction('interface.listes.details_ligne_budget_insight.document_lie')</th>
            </tr>
        </thead>
        <tbody>

        @foreach($lignes as $ligne)
            <tr>
                <td>
                    <a href="{{ route('base_eden.fiche.index', array('paiement', $ligne->id), false) }}">{!! $ligne->id !!}</a>
                </td>
                <td>
                    {!! management('client', $ligne->client_id)->affiche_lien() !!}
                </td>
                <td>
                    {!! montant($ligne->montant, fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif',',', ' ')) !!} {!! maquette('devise_application_symbole') !!}
                </td>
                <td>
                    {!! management('paiement', $ligne->id)->champ('mode_paiement_id')->affiche() !!}
                </td>
                <td>
                    @if(!empty($ligne->document_lie))
                        <a href="{{ route('document.afficher', array($ligne->type_element, $ligne->id_document), false) }}">{!! $ligne->document_lie->chaine_affichage !!}</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
