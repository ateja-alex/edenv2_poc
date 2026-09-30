<section v-show="bloc_affiche == '{{$id}}'">
    <affichage-calendrier ref="calendrier_{{$id}}" :valeurs_par_defaut_tache="{affectation:$root.moi.id,affectations:[$root.moi.id]}" :filtres_pour_fiche="{utilisateur : $root.moi.id}" :afficher_les_filtres="false"></affichage-calendrier>
</section>

@push('donnees_pour_vuejs_mounted')
    this.$on('affichage_module_{{$id}}', () => {
        this.$nextTick(() => {
            this.$refs.calendrier_{{$id}}.met_a_jour_les_dates();
        });
    });
@endpush
