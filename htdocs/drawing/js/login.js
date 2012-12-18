function LoginRequest(userName, password, successCallBack, failCallBack){
	//allFields = $( [] ).add( userName ).add( password );
	var tipsObj = $( "p#loginTips" );
	
	$.get(
		"Controller/loginHandler.php",
		{ "op": "login", "userName": userName, "password":password },
		function(data){
			DebugOutput(data);
			if (data.ret == 1){
				successCallBack();
			}else{
				failCallBack(data);
			}
		},
		"json"
	);
}

function LogoutRequest(){
	$.get(
		"Controller/loginHandler.php",
		{ "op": "logout"},
		function(data){
			//console.log(data);
			DebugOutput(data);
			if (data.ret){ // logout successful
				window.location.replace("loginFrom.html");
			}
		},
		"json"
	);
}

function LoginCheck(){
	$.get(
		"Controller/loginHandler.php",
		{ "op": "islogin"},
		function(data){
			//console.log(data);
			DebugOutput(data);
			if (data.ret == 0){ // not login
				window.location.replace("loginFrom.html?redirect=index.php");
			}
		},
		"json"
	);
}

function RedirectBack(){
	//var url = location.href;
	var matched = location.href.match(/(&|\?)redirect=([^&]+)/);
	if (matched && matched[2]){
		window.location.replace(matched[2]);
	}else{
		window.location.replace("index.php");
	}
}

function UpdateTips( data ) {
	var tips = $( "p#loginTips" );
	tips
		.text( data.error )
		.addClass( "ui-state-highlight" );
	setTimeout(function() {
		tips.removeClass( "ui-state-highlight", 1500 );
	}, 500 );
}