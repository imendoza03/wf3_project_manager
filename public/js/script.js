$(function(){
	$('#form_username').on('blur', function(){
		$username = $(this).val();
		$url = '/username/available';
		
		$.ajax({
			url: $url,
			method: 'POST',
			data: {'username': $username}
		}).done(function(data){
			console.log(data);
		});
	});
});