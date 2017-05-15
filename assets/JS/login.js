function asynchronousTreatment(path,params){
	var request = new XMLHttpRequest();
	request.open("POST", path, true);
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	request.onload = function(e) {
        //document.write(request.responseText);
        var message = document.getElementById('message');
		if (request.responseText !== ''){
            message.innerHTML = JSON.parse(request.responseText);
		}else{
			window.location = '?action=home';
		}
	};//todo make errorMessage an object, and put separate messages next to corresponding fields.
	request.send(params);
}


window.onload = function(){
	var buttonDisplay = document.getElementById('buttonDisplay');
	buttonDisplay.onclick = function(){
		var toDisplay = document.getElementsByTagName('form')[0];
		toDisplay.className = "appearingSlowly";
	};

	var form = document.forms["connect"];
	form.onsubmit = function(){
		var username = encodeURIComponent(form["username"].value);
		var password = encodeURIComponent(form["password"].value);

		var message = document.getElementById('message');

		message.innerHTML = "";


		var params = "username="+username+"&password="+password;
		asynchronousTreatment("?action=login", params);
		

		return false;	
	}
}
