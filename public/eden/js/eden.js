async function erreur(erreur) {

	await alerte_eden(erreur);
}

function info(message) {

	toastr.success(message,vue_instance.traduction('interface.toastr.information'));
}

function erreur_toast(message) {

	toastr.error(message,vue_instance.traduction('interface.toastr.erreur'));
}


// Method loading
function loading(display = true) {

	if(display == true) {

       $('body').css('overflow', 'hidden');
	   $('#loading').show();

	}
	else {

		$('body').css('overflow', '');
		$('#loading').hide();
	}
}

donnees_filtres = [];

$(function(){


   $('[data-toggle="tooltip"]').tooltip();

    $('.datepicker').datepicker({
        format: "dd/mm/yyyy",
        language: 'fr'
    });

    $('.js_datepicker').datepicker({
        format: "dd/mm/yyyy",
        language: 'fr'
    });

    $('body').on('click', '.js_badge_selectionnables .badge', function() {

        if($(this).hasClass('badge-success')) {

            $(this).removeClass('badge-success').addClass('badge-default');
            donnees_filtres.splice($.inArray($(this).attr("data-id"), donnees_filtres), 1);
        }
        else {

            $(this).removeClass('badge-default').addClass('badge-success');
            donnees_filtres.push($(this).attr("data-id"));
        }
	});

	/* Toggle traduction des données */
	$('.js_toggle_mode_parametrage').change(function () {

		if($(this).prop('checked')) {

			$.ajax({
				url: "eden/parametrage/mode/"+1,
				method: "POST",
				data: {
					mode_parametrage: 1
				},
			}).done(function(){
				loading(true);
				location.reload();
			});;
		}
		else {

			$.ajax({
				url: "eden/parametrage/mode/"+0,
				method: "POST",
				data: {
					mode_parametrage: 0
				},
			}).done(function(){
				loading(true);
				location.reload();
			});

		}



	});

	/* Toggle traduction des données */
	$('.js_toggle_traduction_donnees').change(function () {

		if($(this).prop('checked')) {

			$.ajax({
				url: "eden/traductions_donnees/active",
			}).done(function() {

				document.location.reload(true);
			});
		}
		else {

			$.ajax({
				url: "eden/traductions_donnees/desactive",
			}).done(function() {

				document.location.reload(true);
			});

		}

	});

});

$('body').on('click', '.js_fermeture_bloc', function(event) {

	// on essaie de gérer un stopPropagation sans devoir l'écrire pour chaque élément...
	if(event.originalEvent.target.nodeName != 'H4' && event.originalEvent.target.nodeName != 'DIV')
		return;

	var body = $(this).closest('.card');

	if(body.find('.card-body').is(':visible')) {

		$(this).find('span.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');

		body.find('.card-body').slideUp();
	}
	else {

		$(this).find('span.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');

		body.find('.card-body').slideDown();
	}
});

$('body').on('click', '.js_formulaire_deplie_titre', function() {

	var bloc_a_deplier = $(this).closest('div').parent().next();

	if(bloc_a_deplier.is(':visible')) {

		$(this).removeClass('fa-chevron-up');
		$(this).addClass('fa-chevron-down');

		bloc_a_deplier.slideUp();
	}
	else {

		$(this).removeClass('fa-chevron-down');
		$(this).addClass('fa-chevron-up');

		bloc_a_deplier.slideDown();
	}

});

// gestion des checkbox
$('body').on('click', '.js_checkbox_eden', function() {

	if($(this).hasClass('active')) {

		// $(this).find('input').prop('checked', false);
		$(this).removeClass('active');
		$(this).find('.js_checkbox').removeClass('css_checkbox_on').addClass('css_checkbox_off');
	}
	else {

		// $(this).find('input').prop('checked', true);
		$(this).addClass('active');
		$(this).find('.js_checkbox').removeClass('css_checkbox_off').addClass('css_checkbox_on');
	}
});

// gestion d'une classe particulière sur les dropdown qui évite la propagation pour ne pas fermer le dropdown
$('body').on('keyup', '.js_dropdown_stop_propagation', function(e) {

	e.stopPropagation();
});

var partie_droite_repliee = false;

function replie_partie_droite_fiche() {

	if($('#partie_droite_fiche').length > 0){
		$('#partie_droite_fiche').addClass('css_colonne_droite_fiche_repliee');
		$('.content-wrapper').animate({'margin-right': 40});
	}

}

function deplie_partie_droite_fiche() {

	if($('#partie_droite_fiche').length > 0){
		$('#partie_droite_fiche').removeClass('css_colonne_droite_fiche_repliee');
		$('.content-wrapper').animate({'margin-right': 480});
	}

}

async function alerte_eden(message, titre = false, texte_bouton = false){
    
    if(titre === false){
        titre = vue_instance.traduction('interface.alerte.erreur');
    }
    if(texte_bouton === false){
        texte_bouton = vue_instance.traduction('interface.alerte.ok');
    }
	$('#alerte_eden').attr('title', titre);
	$('#alerte_eden').children('p').html(message);
	var buttons = {};

	buttons[texte_bouton] = function() {
		$( this ).dialog( "close" );
	};
	return await new Promise(resolve => {
		$( "#alerte_eden" ).dialog({
			draggable: false,
			modal: true,
			resizable: false,
			position: { my: "center top+5", at: "top", of: window },
			buttons: buttons,
			close: function( event, ui ) {
				resolve(true);
			}
		});
	});

}
async function confirm_eden(message = false, titre = false, bouton_oui = false, bouton_non = false){

	if(message === false){
		message = vue_instance.traduction('interface.listes.etes_vous_certain');
	}
    if(titre === false){
        titre = vue_instance.traduction('interface.alerte.attention');
    }
    if(bouton_oui === false){
        bouton_oui = vue_instance.traduction('interface.modales.oui');
    }
    if(bouton_non === false){
        bouton_non = vue_instance.traduction('interface.modales.non');
    }
    
	$('#alerte_eden').attr('title', titre);
	$('#alerte_eden').children('p').html(message);
	var buttons = {};

	return await new Promise(resolve => {
		buttons[bouton_oui] = function() {
			resolve(true);
			$( this ).dialog( "close" );
		};
		buttons[bouton_non] = function() {
			resolve(false);
			$( this ).dialog( "close" );
		};
		$( "#alerte_eden" ).dialog({
			draggable: false,
			modal: true,
			resizable: false,
			closeText: "hide",
			position: { my: "center top+5", at: "top", of: window },
			buttons: buttons,
		});
	});

}