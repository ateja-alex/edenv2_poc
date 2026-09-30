$(function () {
	$('[data-toggle="popover"]').popover();

	// Toggle de la popover du filtre sélectionner
	$('.js_dropdown_filtres').click(function() {
		var this_popover = $(this).next('.js_block_popover_filtre');
		$('.js_block_popover_filtre').not(this_popover).removeClass('deploy').hide('fast');
		this_popover.toggleClass('deploy').show('fast');
	});

	// Ferme le popover dès que l'on clique ailleurs
	$(document).on('click', function(e) {
		var target = $(e.target);
		var datepicker_class = ".day, .dow, .prev, .next, .month, .datepicker-months, .today, .datepicker-years, .year, .new, .clear, .datepicker-switch, .datepicker-days";
		if(!target.is($('.js_block_popover_filtre, .js_dropdown_filtres').find('*').addBack()) && !target.is(datepicker_class)) {
			$('.js_block_popover_filtre').removeClass('deploy').hide('fast');
		}
	});
	// Js ouvrir panel plus de filtres
	$('.js_toggle_panel_plus_de_filtres').click(function() {
		$('.js_panel_plus_de_filtres').toggleClass('css_panel_filtres_replier');
	});
	// Js fermer panel plus de filtres
	$('.js_close_panel_plus_de_filtres').click(function() {
		$('.js_panel_plus_de_filtres').toggleClass('css_panel_filtres_replier');
	});
	// Disable les dates si filtre rapide sélectionner
	$('.js_radio_filtre_date').click(function() {

		nom_filtre_sql = $(this).data('filtre-sql');
		// on supprime la valeur des dates et désactive les champs date
		$('.js_input_filtre_liste_'+nom_filtre_sql).prop('disabled', true);
		$('.js_input_filtre_liste_'+nom_filtre_sql).val('');
		// on affiche le bouton pour supprimer le filtre actif en fonction du nom sql
		$('.js_annuler_filtre_rapide[data-filtre-sql='+nom_filtre_sql+']').addClass('deploy');
	});
	// Supprimer filtre actif
	$('.js_annuler_filtre_rapide').click(function() {
		nom_filtre_sql = $(this).data('filtre-sql');
		$('input[type=radio][name=radio_'+nom_filtre_sql+']').prop('checked', false);
		$('.js_input_filtre_liste_'+nom_filtre_sql).prop('disabled', false);
		$('.js_input_filtre_liste_'+nom_filtre_sql).val('');
		// on masque le bouton pour supprimer le filtre actif en fonction du nom sql
		$('.js_annuler_filtre_rapide[data-filtre-sql='+nom_filtre_sql+']').removeClass('deploy');
	});
	// On affiche la liste des utilisateurs easydev dans les filtres
	$('.js_sous_titre_filtre_utilisateurs').click(function() {
		type_utilisateur = $(this).data('utilisateurs');
		$('.js_liste_user_'+type_utilisateur+'_popover').slideToggle(350);
		$(this).toggleClass('rotate');
	});

	//Gestion des checkbox des dates
	$('.filtre_date_checkbox').change(function() {

		$(this).closest('.css_liste_checkbox_popover').find('.filtre_date_checkbox').not(this).prop('checked', false);

	});
})
	