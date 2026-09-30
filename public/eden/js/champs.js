// pour les typeahead
function active_selection_element(type_element, nom_champ, callback) {

	var callback = (typeof callback !== 'undefined') ? callback : function() {};
	
	var accountBloodhound = new Bloodhound({
		

		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,

		remote: {
			url: 'eden/element/recherche/'+type_element+'/%QUERY',
			wildcard: '%QUERY',
			transport: function (opts, onSuccess, onError) {
				
				var url = opts.url.split("#")[0];
				var query = opts.url.split("#")[1];
				
				$.ajax({
					
					url: url,
					data: "search=" + query,
					type: "GET",
					dataType: "json",
					success: onSuccess,
					error: onError,
				});
			}
		}
	});
	
	$('input[name='+nom_champ+'_recherche]').typeahead(null, {
		
		limit: 50,
		name: 'liste_elements',
		display: 'affichage_pour_recherche',
		source: accountBloodhound 
	}).bind('typeahead:select', function (ev, element) {
		
		// on va chercher l'élément
		$.ajax({
			
			url: "eden/element/"+type_element+"/"+element.id,
			dataType: "json"
		}).done(function(element) {
			
			$('input[name='+nom_champ).val(element.id);
			$('#'+nom_champ+'_affichage_resultat').html(element.affiche_lien);
			
			$('input[name='+nom_champ+']').closest('.twitter-typeahead').find('.tt-hint').val('');
			$('input[name='+nom_champ+'_recherche]').val('');
			
			$('input[name='+nom_champ).change();
			
			
			callback(element);
		});

	});
}

var tinymce_editors = [];
var tinymce_editors_info = {};

function initialisation_champs_wysiwyg(){

	$('textarea.js_wysiwyg').each(function () {

		// on tente de modifier les IDS pour gérer automatiquement les doublons
		length = 15;

		var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

		if (!length) {
			length = Math.floor(Math.random() * chars.length);
		}

		var nouvel_id = '';
		for (var i = 0; i < length; i++) {
			nouvel_id += chars[Math.floor(Math.random() * chars.length)];
		}

		$(this).attr('id', nouvel_id);

		if ($(this).attr('id') == undefined || $(this).attr('id') == '') {

			alert("Un textarea avec éditeur WYSIWYG n'a pas d'ID");
			console.log($(this));
		}

		if ($(this).attr('type_element') == undefined || $(this).attr('type_element') == '') {

			alert("Il y a un textarea WYSIWYG dont l'attr type_element n'est pas renseigné");
			console.log($(this));
		}

		if ($(this).attr('nom_sql') == undefined || $(this).attr('nom_sql') == '') {

			alert("Il y a un textarea WYSIWYG dont l'attr nom_sql n'est pas renseigné");
			console.log($(this));
		}

		if ($('textarea#' + $(this).attr('id')).length > 1) {

			alert("Erreur, il y a plusieurs textareas avec l'ID " + $(this).attr('id'));
		}

		var id_liste = null;

		if (!$(this).parents('.js_liste').hasClass('kanban') && $(this).parents('.js_liste').length) {
			id_liste = $(this).parents('.js_liste').first().attr('id_liste');
		}

		var id_textarea = $(this).attr('id');
		var type_element = $(this).attr('type_element');
		var composant = $(this).attr('composant');
		var nom_sql = $(this).attr('nom_sql');

		tinymce.init({
			selector: 'textarea#' + id_textarea,
			plugins: 'advlist autolink link image lists charmap print preview code',
			relative_urls: false,
			remove_script_host: false,
			force_br_newlines: true,
			force_p_newlines: false,
			forced_root_block: '',
			setup: function (ed) {

				ed.on('change', function (e) {

					//cas spécifique des listes
					if (id_liste != null) {

						vue_instance.$refs['liste_libre_' + id_liste].$data[type_element][nom_sql] = ed.getContent();
						vue_instance.$forceUpdate();

						return;
					}
					// c'est le cas spécifique de la page paramètre
					if (vue_instance.$data[type_element] == undefined && vue_instance.$data.parametre != undefined) {

						$('#' + id_textarea).val(ed.getContent());
						vue_instance.$data.parametre[nom_sql] = ed.getContent();
						vue_instance.$forceUpdate();
						return;
					}

					if (composant != undefined) {

						vue_instance.$refs[composant][type_element][nom_sql] = ed.getContent();

						vue_instance.$forceUpdate();

						return;
					}

					vue_instance.$data[type_element][nom_sql] = ed.getContent();

					vue_instance.$forceUpdate();


				});
			}
		});

		tinymce_editors.push(id_textarea);

		tinymce_editors_info[id_textarea] = {
			'type_element' : type_element,
			'nom_sql' : nom_sql,
		};

		$(document).on('focusin', function (e) {
			if ($(e.target).closest(".mce-window").length) {
				e.stopImmediatePropagation();
			}
		});
	});

}

$(document).ready(function() {

	$('.js_active_recherche_element').each(function() {

		active_selection_element($(this).attr('recherche_type_element'), $(this).attr('recherche_nom_sql'));
	});

	// on active les champs WYSIWYG
	initialisation_champs_wysiwyg();
});