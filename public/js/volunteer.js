self.addEventListener('message', function(e) {	

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			self.postMessage(this.responseText);
		}
		// self.postMessage("/"+e.data.data);
	};
	xhttp.open(e.data.method, "/"+e.data.url, true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.setRequestHeader('X-CSRF-TOKEN', e.data.token);
	xhttp.send(JSON.stringify(e.data.data));
	
}, false);