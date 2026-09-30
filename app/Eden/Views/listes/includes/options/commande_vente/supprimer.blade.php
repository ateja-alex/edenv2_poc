<template @if(fonctionnalite('gescom_commande_vente_annulable_non_supprimable') == 'annulable_non_supprimable') v-if="ligne.element.valide != 1" @endif>
    @include('eden::listes.includes.options.supprimer')
</template>