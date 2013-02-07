jQuery(document).ready(function() {

	function toggleFAQ() {

		var speed = 400;

		$('div.faqContent').each(function() {
			$(this).find('div.faqAnswer').hide();
		});

		$('.faqItem h3').click(function (event) {
			event.preventDefault();
			$(this).parent().children('.faqAnswer').stop(true, true).slideToggle(speed);

		});

	}

	toggleFAQ();

});