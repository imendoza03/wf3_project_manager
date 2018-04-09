function validateUsername(userName) 
{
	$.post(
		'/username/available',
		{
			username: userName
		},
		function(responseData) {
			$('.username-validation').remove();
			
			if (responseData.available) {
				$('label[for="form_username"]').append(
						'<span class="available-username username-validation"> available</span>'
				);
				
				return;
			}
			
			$('label[for="form_username"]').append(
					'<span class="unavailable-username username-validation"> unavailable</span>'
			);
		}
	);
}

$('#form_username').on('keyup', function(){
	validateUsername($(this).val());
});