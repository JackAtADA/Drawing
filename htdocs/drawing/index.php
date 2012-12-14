<?php ?>

<html lang="us">
<head>
	<meta charset="utf-8">
	<title>Drawing DateBase</title>
	<link href="css/smoothness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
	<script src="js/jquery-1.8.3.js"></script>
	<script src="js/jquery-ui-1.9.2.custom.js"></script>
	<script src="js/login.js"></script>
	<script>
	// js UI
	function DialogSubmit(userName, password) {
		LoginRequest(userName, password, 
			function(){$("#dialog-form").dialog("close")},
			UpdateTips
		);
	}
	$(document).ready(function(){
	
		LoginCheck(); // it will redirect to loginFrom.html if the user is not login.
		
		
		$( "#Login" )
            .button()
            .click(function() {
                $( "#dialog-form" ).dialog( "open" );
            });
		$( "#Logout" )
            .button()
            .click(LogoutRequest);
			
		var userName = $( "#userName" ), password = $( "#password" );
		//var tipsObj = $( "p#loginTips" );
		allFields = $( [] ).add( userName ).add( password );
		
		$( "#dialog-form" ).dialog({
			autoOpen: false,
			height: 350,
			width: 400,
			modal: true,
			buttons: {
				"Login": function() { 
					DialogSubmit(userName.val(), password.val() ); 
				},
				"Cancel": function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				allFields.val( "" ).removeClass( "ui-state-error" );
			}
		});
		
		$('#password').bind('keypress', function(e) {
			var code = (e.keyCode ? e.keyCode : e.which);
			if(code == 13) { //Enter keycode
			   //console.log("Enter");
			   DialogSubmit(userName.val(), password.val() ); 
			}
		});
	});
	
	</script>
</head>
<body>
<button id="Login">Login</button>
<button id="Logout">Logout</button>
<div id="dialog-form" title="Login">
	<p id="loginTips"></p>
    <form>
    <fieldset>
        <label for="userName">Name</label>
        <input type="text" name="userName" id="userName" class="text ui-widget-content ui-corner-all" />
        <label for="password">Password</label>
        <input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
    </fieldset>
    </form>
</div>
</body>