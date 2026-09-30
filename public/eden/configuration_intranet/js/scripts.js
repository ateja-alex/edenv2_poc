(function($) {
  "use strict";
  
$('.profile-link-box').click(function() {
    $('.profile-link').addClass('active');
    $('.on').addClass('off');
    $('.off').removeClass('on').delay(500).queue(function(next){
        $(this).addClass('display-none');
        $('.back').css('opacity','1');
        next();
    });
    $('.profile').removeClass('off').delay(0).queue(function(next){
        $(this).addClass('on').removeClass('display-none');
        next();
    });
});

$('.note-link-box').click(function() {
    $('.note-link').addClass('active');
    $('.on').addClass('off');
    $('.off').removeClass('on').delay(500).queue(function(next){
        $(this).addClass('display-none');
        $('.back').css('opacity','1');
        next();
    });
    $('.note').removeClass('off').delay(0).queue(function(next){
        $(this).addClass('on').removeClass('display-none');
        next();
    });
});

$('.note-liste-link-box').click(function() {
    $('.note-liste-link').addClass('active');
    $('.on').addClass('off');
    $('.off').removeClass('on').delay(500).queue(function(next){
        $(this).addClass('display-none');
        $('.back').css('opacity','1');
        next();
    });
    $('.note-liste').removeClass('off').delay(0).queue(function(next){
        $(this).addClass('on').removeClass('display-none');
        next();
    });
});

$('.portfolio-link-box').click(function() {
    $('.portfolio-link').addClass('active');
    $('.on').addClass('off');
    $('.off').removeClass('on').delay(500).queue(function(next){
        $(this).addClass('display-none');
        $('.back').css('opacity','1');
        next();
    });
    $('.portfolio').removeClass('off').delay(0).queue(function(next){
        $(this).addClass('on').removeClass('display-none');
        next();
    });
});

$('.education-link-box').click(function() {
    $('.education-link').addClass('active');
    $('.on').addClass('off');
    $('.off').removeClass('on').delay(500).queue(function(next){
        $(this).addClass('display-none');
        $('.back').css('opacity','1');
        next();
    });
    $('.education').removeClass('off').delay(0).queue(function(next){
        $(this).addClass('on').removeClass('display-none');
        next();
    });
});

$('.experience-link-box').click(function() {
    $('.experience-link').addClass('active');
    $('.on').addClass('off');
    $('.off').removeClass('on').delay(500).queue(function(next){
        $(this).addClass('display-none');
        $('.back').css('opacity','1');
        next();
    });
    $('.experience').removeClass('off').delay(0).queue(function(next){
        $(this).addClass('on').removeClass('display-none');
        next();
    });
});

$('.contact-link-box').click(function() {
    $('.contact-link').addClass('active');
    $('.on').addClass('off');
    $('.off').removeClass('on').delay(500).queue(function(next){
        $(this).addClass('display-none');
        $('.back').css('opacity','1');
        next();
    });
    $('.contact').removeClass('off').delay(0).queue(function(next){
        $(this).addClass('on').removeClass('display-none');
        next();
    });
});

<!-- Tooltip -->
$('[data-toggle="tooltip"]').tooltip('hide');

  // Author code here
})(jQuery);


var functions;

var disabled_dates = [];
var closed_days = [];

// Au chargement de la page
$(document).ready(function () {

	Date.prototype.yyyymmdd = function() {
		var mm = this.getMonth() + 1; // getMonth() is zero-based
		var dd = this.getDate();

		return [this.getFullYear(),
		      (mm>9 ? '' : '0') + mm,
		      (dd>9 ? '' : '0') + dd
		     ].join('');
	};

	closed_days.push({d: 1,		m: 1});
	closed_days.push({d: 1,		m: 5});
	closed_days.push({d: 8,		m: 5});
	closed_days.push({d: 14,	m: 7});
	closed_days.push({d: 15,	m: 8});
	closed_days.push({d: 1,		m: 11});
	closed_days.push({d: 11,	m: 11});
	closed_days.push({d: 25,	m: 12});

	for(year = 2017; year <= 2022; year++) {

		for(i in closed_days) {
			var month = closed_days[i].m - 1;
			var day = closed_days[i].d;

			disabled_dates.push(new Date(year,month,day).yyyymmdd());
		}
	}

	disabled_dates.push(new Date(2017,(4-1),17).yyyymmdd());
	disabled_dates.push(new Date(2017,(5-1),25).yyyymmdd());
	disabled_dates.push(new Date(2017,(6-1),5).yyyymmdd());

	disabled_dates.push(new Date(2018,(4-1),2).yyyymmdd());
	disabled_dates.push(new Date(2018,(5-1),10).yyyymmdd());
	disabled_dates.push(new Date(2018,(5-1),21).yyyymmdd());

	disabled_dates.push(new Date(2019,(4-1),22).yyyymmdd());
	disabled_dates.push(new Date(2019,(5-1),30).yyyymmdd());
	disabled_dates.push(new Date(2019,(6-1),10).yyyymmdd());

	disabled_dates.push(new Date(2020,(4-1),13).yyyymmdd());
	disabled_dates.push(new Date(2020,(5-1),21).yyyymmdd());
	disabled_dates.push(new Date(2020,(6-1),1).yyyymmdd());

	disabled_dates.push(new Date(2021,(4-1),5).yyyymmdd());
	disabled_dates.push(new Date(2021,(5-1),13).yyyymmdd());
	disabled_dates.push(new Date(2021,(5-1),24).yyyymmdd());

	disabled_dates.push(new Date(2022,(4-1),18).yyyymmdd());
	disabled_dates.push(new Date(2022,(5-1),26).yyyymmdd());
	disabled_dates.push(new Date(2022,(6-1),6).yyyymmdd());

    // Affichage calendrier
    $('.input_datepicker').datepicker({
        todayBtn: "linked",
        todayHighlight: true,
        format: "dd/mm/yyyy",
        weekStart: 1
		// beforeShowDay: function(date) {

	        // return ($.inArray(date.yyyymmdd(), disabled_dates) == -1);
	    // }
    });
});

function une_seul_journee() {

    if($('#debut_ampm').val() == '3') {

        $('#date_de_fin_container').hide();
        $('#date_fin').val($('#date_debut').val());
        $('#fin_ampm').val(2);
    }
    else {

        $('#date_de_fin_container').show();
    }
}

$('body').on('change', '#date_debut', function() {

    une_seul_journee();
});

$('body').on('change', '#debut_ampm', function() {

    une_seul_journee();
});
