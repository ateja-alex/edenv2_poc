@push('composants_vue')
    @foreach (\App\Eden\Variables::$documents_gescom_lignes as $type_document)
        @php
            $module = 'fiche_'.$type_element.'_'.$type_document;
        @endphp
        @if (!empty($listes_sur_fiche['commerce_lignes'][$module]))
            <script type="text/javascript" src="{{ '/storage/composants/liste_libre_'.$listes_sur_fiche['commerce_lignes'][$module]['liste_libre']->id.'.js' }}?version={{parametre('version_composants')}}"></script>
        @endif
    @endforeach
@endpush

<commerce :structure="[{
        type_flux: 'vente_lignes',
        nom_type_flux: traduction('module_sur_fiche.commerce_lignes.vente'),
        listes: listes_commerce_lignes.filter(l=>l.liste_libre.type_element.includes('vente')),
        onglet_defaut: '{!! isset($structure['options']['commerce_vente_defaut']) ? $structure['options']['commerce_vente_defaut'].'_lignes' : '' !!}',
    },
    {
        type_flux: 'achat_lignes',
        nom_type_flux: traduction('module_sur_fiche.commerce_lignes.achat'),
        listes: listes_commerce_lignes.filter(l=>l.liste_libre.type_element.includes('achat')),
        onglet_defaut: '{!! isset($structure['options']['commerce_achat_defaut']) ? $structure['options']['commerce_achat_defaut'].'_lignes' : '' !!}',
    }]" :mode_parametrage="mode_parametrage" :filtres_pour_fiche="filtres_pour_fiche" :modele_par_defaut="modele_par_defaut_fiche"></commerce>

@push('donnees_pour_vuejs_data')
	listes_commerce_lignes: {!! collect(array_values($listes_sur_fiche['commerce_lignes'])) !!},
	mode_parametrage: {{ admin() || mode_parametrage() ? 1 : 0 }},
@endpush