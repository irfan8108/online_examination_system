self.addEventListener('message', function(e) {	

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			self.postMessage(JSON.parse(this.responseText));
		}
		// self.postMessage(e.data);
	};
	xhttp.open("POST", "/examSession", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.setRequestHeader('X-CSRF-TOKEN', e.data);
	xhttp.send();
	
}, false);