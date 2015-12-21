function get(name) {
	var url = window.location.href;
    var urlArray = url.split('?');
	window.location.href = 'http://' + window.location.host+'/test/crm/contact_hash.php'+'?tagList=^'+name;
}

function phone(phone_num) {
	window.location.href = 'tel://' + phone_num;
}