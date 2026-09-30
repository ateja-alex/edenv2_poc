@if(table_libre($type_element)->fiche == 1)
    @include('eden::listes.includes.options.lien')
@endif
<template v-if="ligne.element.client_id > 0 || ligne.element.fournisseur_id > 0">
    <a :href="'eden/fiche/'+(ligne.element.client_id > 0 ? 'client' : 'fournisseur')+'/'+(ligne.element.client_id > 0 ? ligne.element.client_id : ligne.element.fournisseur_id)">
        <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
            <span class="far fa-building "
                  data-toggle="tooltip"
                  :title="$root.traduction('interface.listes.fiche')+' '+$root.traduction('tables_libres.'+(ligne.element.client_id > 0 ? 'client' : 'fournisseur')+'.element')">
            </span>
        </span>
    </a>
</template>