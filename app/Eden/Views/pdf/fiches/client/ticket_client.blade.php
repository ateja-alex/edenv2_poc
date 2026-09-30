<h3>{!! traduction('pdf.ticket_client.titre', $langue) !!}</h3>
<table class="css_tableau_ca_indicatifs">
    <tbody>
        <tr>
            <td>
                <div class="css_chiffre_indicateur">
                    {{ $donnees['nombre_tickets_total'] }}
                </div>
                <div class="css_separateur_indicateur"></div>
                <span>{!! traduction('pdf.ticket_client.ticket_total', $langue) !!}</span>
            </td>
            <td>
                <div class="css_chiffre_indicateur">
                    {{ $donnees['nombre_tickets_annee'] }}
                </div>
                <div class="css_separateur_indicateur"></div>
                <span>{!! traduction('pdf.ticket_client.ticket_total_annee', $langue) !!} {{ date('Y')}}</span>
            </td>
        </tr>
    </tbody>
</table>

