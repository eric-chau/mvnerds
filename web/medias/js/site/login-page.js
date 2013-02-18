$(document).ready(function()
{
	// Activation de la modal au click sur le lien "Mot de passe oublié ?"
	var $forgotPasswordModal = $('div#forgot-password-modal');
	$('a.forgot-pwd').on('click', function(event) {
		event.preventDefault();
		$forgotPasswordModal.modal('show');
	});
 
	$('form#forgot-password-form').submit(function(event)
	{
		event.preventDefault();
		var $emailInput = $('input#email_field'),
			$loader = $(this).find('i.loader'),
			$sendButton = $(this).find('button'),
			$forbidIcon = $(this).find('i.icon-ban-circle'),
			$divError = $(this).parent().find('div.error');

		if ($sendButton.hasClass('disabled')) {
			return false;
		}

		var $email = $.trim($emailInput.val());
		if ($email != '' && validateEmail($email)) {
			$divError.slideUp();
			$forbidIcon.addClass('hide');
			$loader.removeClass('hide');
			$emailInput.attr('disabled', 'disabled');
			$sendButton.addClass('disabled');

			$.ajax({
				url: Routing.generate('site_forgot_password', {'_locale': locale, 'email': $emailInput.val()}),
				type: 'get',
				dataType: 'json',
				success: function(response) {
					if (response) {
						$forgotPasswordModal.find('div.index-content').hide().remove();
						$forgotPasswordModal.find('div.success-content').show();
					}
					else {
						$divError.slideDown();
						$loader.addClass('hide');
						$emailInput.removeAttr('disabled');
						$sendButton.removeClass('disabled');
						$emailInput.focus();
					}
				}
			});
		}
		else {
			$forbidIcon.removeClass('hide');
		}
	});
});
