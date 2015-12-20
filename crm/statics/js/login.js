var login = {
	login : function(username, password) {
		$.post('', {username:username, password:password}, function(result){
			if (result.data == 0) {
				window.location.href = 'quan.php';
			} else{
				
			}
		})
	},
};
