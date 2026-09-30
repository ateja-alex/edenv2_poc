@if(!empty($entite_management->modele))
<p class="text-center css_footer_text">
            {{ $entite_management->modele->nom }} {{ $entite_management->modele->statut_juridique }} {!! traduction('document.pdf.footer.au_capital_de') !!} {{ $entite_management->modele->capital }} {!! maquette('devise_application_symbole') !!}
            <br>
            {{ $entite_management->modele->registre_immatriculation }} | {!! traduction('document.pdf.footer.tva_intracommunautaire') !!} : {{ $entite_management->modele->numero_tva }}
        </p>

@endif