<template @if(fonctionnalite('gescom_suppression_facture_valide') == 'empecher_suppression') v-if="ligne.element.valide != 1" @endif>
    @include('eden::listes.includes.options.supprimer')
</template>