Vue.filter('nl2br', function (value) {

	if (!value)
		return '';

	return (value + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2')
});

Vue.filter('retraite_caracteres_speciaux', function (value) {

	if (!value)
		return '';

	var accents_array = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å',
		'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
		'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'é','é', // (le dernier 'é' n'est pas un doublon, c'est un caractère spécial)
		'Ç', 'ç',
		'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï',
		'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü',
		'ÿ',
		'Ñ', 'ñ'];

	var sans_accents = 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeeeCcIIIIiiiiUUUUuuuuyNn'.split('');

	var accents = new RegExp(accents_array.join("|"),"gi");

	var nouvelle_chaine = value.replace(accents, (matched) => {
		var index = accents_array.indexOf(matched);

		return sans_accents[index];
	});
	
	return nouvelle_chaine.replace(/[^A-Za-z0-9]/g, '');
});

Vue.filter('date', function (value) {

	if (!value)
		return '';

	return moment(String(value)).format('DD/MM/YYYY')

});

Vue.filter('date_relatif', function (value) {

	if (!value)
		return '';

	moment.lang('fr');

	return moment(value, "YYYY-MM-DD").calendar();

});

Vue.filter('date_relatif_sans_heure', function (value) {

	if (!value)
		return '';

	moment.lang('fr');

	return moment(value).format("dddd Do MMMM YYYY");

});

Vue.filter('datetime', function (value) {

	if (!value)
		return '';

	return moment(String(value)).format('DD/MM/YYYY à MM:mm:ss')

});

Vue.filter('time', function (value) {

	if (!value)
		return '';

	return moment(String(value)).format('MM:mm:ss')

});


Vue.filter('datetime_relatif', function (value) {

	if (!value)
		return '';

	moment.lang('fr');

	return moment(value, "YYYY-MM-DD HH:mm:ss").calendar();

});

Vue.filter('devises', function (value) {

	if (!value)
		return '0,00';

	return value.toFixed(2);

});

Vue.filter('nombre_couleur', function (nombre) {

	if (!nombre)
		return '';

	if(nombre >= 0) {

		return '<span style="color: #669e24;">'+nombre+'</span>';
	}
	else {

		return '<span style="color: #ed6f56;">'+nombre+'</span>';
	}
});

Vue.filter('montant', function (nombre,devise = null) {

	if (nombre === null || nombre == undefined || nombre == '')
		nombre = "0,00";

	var devise_application_iso = null;

	if(devise != null) {
		devise_application_iso = devise;
	}
	else {

		var script_tag = document.getElementById('filtres_vuejs_erp');

		if (script_tag != null)
			devise_application_iso = script_tag.getAttribute("devise_application_iso");
	}

	if(devise_application_iso != null)
		return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: devise_application_iso }).format(parseFloat(nombre).toFixed(2));

	return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(parseFloat(nombre).toFixed(2));

	// return parseFloat(nombre).toFixed(2).replace('.', ',');
});