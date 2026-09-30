
@extends('eden::mails.template_v2')

@section('explication')

    {!! traduction('mails.expressions.bonjour', $destinataire->langue) !!},

    <h2>{!! traduction('mails.alerte_stocks_articles.seuil_alerte', $destinataire->langue) !!}</h2>
    <table style="border-collapse: collapse;width: 100%;">
        <thead>
            <th style="border: 1px solid #ddd;  padding: 8px;text-align:left;">{!! traduction('mails.alerte_stocks_articles.code_article', $destinataire->langue) !!}</th>
            <th style="border: 1px solid #ddd;  padding: 8px;text-align:left;">{!! traduction('mails.alerte_stocks_articles.article', $destinataire->langue) !!}</th>
            <th style="border: 1px solid #ddd;  padding: 8px;text-align:left;">{!! traduction('mails.alerte_stocks_articles.stock_seuil_alerte', $destinataire->langue) !!}</th>
            <th style="border: 1px solid #ddd;  padding: 8px;text-align:left;">{!! traduction('mails.alerte_stocks_articles.stock_actuel', $destinataire->langue) !!}</th>

        </thead>
        <tbody>
            @foreach($stocks_articles['seuil_alerte'] as $stock)
                <tr>
                    <td style="border: 1px solid #ddd;  padding: 8px;">{{ $stock->code_article }}</td>
                    <td style="border: 1px solid #ddd;  padding: 8px;">{{ $stock->designation }}</td>
                    <td style="border: 1px solid #ddd;  padding: 8px;">{{ $stock->stock_seuil_alerte }}</td>
                    <td style="border: 1px solid #ddd;  padding: 8px;"><b>{{ $stock->stock_actuel }}</b></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>{!! traduction('mails.alerte_stocks_articles.prochainement_disponible', $destinataire->langue) !!}</h2>
    <table style="border-collapse: collapse;width: 100%;">
        <thead>
            <th style="border: 1px solid #ddd;  padding: 8px;text-align:left;">{!! traduction('mails.alerte_stocks_articles.code_article', $destinataire->langue) !!}</th>
            <th style="border: 1px solid #ddd;  padding: 8px;text-align:left;">{!! traduction('mails.alerte_stocks_articles.article', $destinataire->langue) !!}</th>
            <th style="border: 1px solid #ddd;  padding: 8px;text-align:left;">{!! traduction('mails.alerte_stocks_articles.date_de_disponibilite', $destinataire->langue) !!}</th>
        </thead>
        <tbody>
            @foreach($stocks_articles['produits_prochainement_dispo'] as $stock)
                <tr>
                    <td style="border: 1px solid #ddd;  padding: 8px;">{{ $stock->code_article }}</td>
                    <td style="border: 1px solid #ddd;  padding: 8px;">{{ $stock->designation }}</td>
                    <td style="border: 1px solid #ddd;  padding: 8px;"><b> {{ strftime("%d/%m/%G", strtotime($stock->date_de_disponibilite)) }}</b></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection