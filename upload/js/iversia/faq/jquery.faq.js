jQuery(document).ready(function() {

	function toggleFAQ() {

		var speed = 400;

		$('div.faqContent').each(function() {
			$(this).find('.faqSlide div.faqAnswer').hide();
		});

		$('.faqSlide h3').click(function (event) {
			event.preventDefault();
			$(this).parent().children('.faqAnswer').stop(true, true).slideToggle(speed);

		});

	}

	toggleFAQ();

});