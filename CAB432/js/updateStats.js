function updateStats(result) {
	var results = JSON.parse(result); //Convert 'JSON string result' to an actual JSON object in javascript
	$('#ajaxResults').empty();
	$('#ajaxResults').append(result);
	for (var term in results) {
		$('#ajaxResults').append(term);
	}
}