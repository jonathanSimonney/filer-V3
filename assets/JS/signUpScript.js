function asynchronousTreatment(path,params){
    var request = new XMLHttpRequest();
    request.open("POST", path, true);
    request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    request.onload = function(e) {
        //document.write(request.responseText); //only to debug! (took me a long time to understand it)
        var array = JSON.parse(request.responseText);
        message.innerHTML = array[0];
        if (array["formOk"]) {
            message.className = "message green";
            window.setTimeout(
                function(){ window.location = "?action=login"; },3000
            );
        }else{
            message.className = "message red";
        }
    };
    request.send(params);
}

window.onload = function(){
    var form = document.forms["signUp"];
    form.onsubmit = function(){
        var username = encodeURIComponent(form["username"].value);
        var email = encodeURIComponent(form["email"].value);
        var password = encodeURIComponent(form["password"].value);
        var confirmationOfPassword = encodeURIComponent(form["confirmationOfPassword"].value);
        var indic = encodeURIComponent(form["indic"].value);
        var message = document.getElementById('message');

        message.innerHTML = "";

        var params = "username="+username+"&email="+email+"&password="+password+"&confirmationOfPassword="+confirmationOfPassword+"&indic="+indic;
        asynchronousTreatment("?action=register", params);

        return false;
    }
}
