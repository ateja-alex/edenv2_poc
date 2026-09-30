@push('composants_vue')
    @foreach (\App\Eden\Variables::$documents_gescom as $type_document)
        @php
            $module = 'fiche_'.$type_element.'_'.$type_document;
        @endphp
        @if (!empty($listes_sur_fiche['commerce'][$module]))
            <script type="text/javascript" src="{{ '/storage/composants/liste_libre_'.$listes_sur_fiche['commerce'][$module]['liste_libre']->id.'.js' }}?version={{parametre('version_composants')}}"></script>
        @endif
    @endforeach
@endpush

<commerce :structure="[{
        type_flux: 'vente',
        nom_type_flux: traduction('module_sur_fiche.commerce.vente'),
        listes: listes_commerce.filter(l=>l.liste_libre.type_element.includes('vente')),
        onglet_defaut: '{!! $structure['options']['commerce_vente_defaut'] ?? '' !!}',
    },
    {
        type_flux: 'achat',
        nom_type_flux: traduction('module_sur_fiche.commerce.achat'),
        listes: listes_commerce.filter(l=>l.liste_libre.type_element.includes('achat')),
        onglet_defaut: '{!! $structure['options']['commerce_achat_defaut'] ?? '' !!}',
    }]" :mode_parametrage="mode_parametrage" :filtres_pour_fiche="filtres_pour_fiche" :modele_par_defaut="modele_par_defaut_fiche"></commerce>

@push('donnees_pour_vuejs_data')
	listes_commerce: {!! collect(array_values($listes_sur_fiche['commerce'])) !!},
	mode_parametrage: {{ admin() || mode_parametrage() ? 1 : 0 }},
@endpush