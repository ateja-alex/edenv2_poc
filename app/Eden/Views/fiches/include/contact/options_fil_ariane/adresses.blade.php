<a v-for="adresse in adresses_du_contact" :href="'https://www.google.fr/maps/?q='+(adresse.societe != undefined ? adresse.societe : '') +' '+adresse.adresse+' '+(adresse.completement != undefined ? adresse.completement : '')+' '+(adresse.code_postal != undefined ? adresse.code_postal : '')+' '+adresse.ville" class="css_action_icon primaire fas fa-map-marker-alt" title="{{ traduction('module_sur_fiche.fiche.contact.afficher_adresse') }}" data-toggle="tooltip"></a>

@push('donnees_pour_vuejs_data')
    adresses_du_contact: {!! $adresses_du_contact !!},
@endpush