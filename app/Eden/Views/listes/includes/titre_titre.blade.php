<template v-if="liste.type_element">
    <span v-if="!liste.modele_liste_libre.id_rapport" v-html="$root.traduction('tables_libres.'+liste.type_element+'.nom_table')"></span>
    <span v-else v-html="$root.traduction('rapport.'+liste.modele_liste_libre.id_rapport+'.titre')"></span>
</template>
