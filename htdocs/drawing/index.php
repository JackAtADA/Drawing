<?php ?>

<html lang="us">
<head>
	<meta charset="utf-8">
	<title>Drawing DateBase</title>
	<link href="css/smoothness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
	<script src="js/jquery-1.8.3.js"></script>
	<script src="js/jquery-ui-1.9.2.custom.js"></script>
	<script src="js/login.js"></script>
	<script src="js/main.js"></script>
	<script>
	// js UI
	function DialogSubmit(userName, password) {
		LoginRequest(userName, password, 
			function(){$("#dialog-form").dialog("close")},
			UpdateTips
		);
	}
	function SubmitSearchFrom(e){
		e.preventDefault();
		var drawingNo = $( "#drawingNo" );
		var description = $( "#description" );
		var revisionDate = $( "#revisionDate");
		var dateOperation = $( "#dateOperation" );
		
		searchField = {
			"drawingNo": drawingNo.val(), 
			"description": description.val(),
			"revisionDate": revisionDate.val(),
			"dateOperation": dateOperation.val(),
			"search": "range"
		};
		
		$.get("Controller/searchHandler.php", searchField, function(data){
			//DebugOutput( searchField );
			//DebugOutput( data );
			
			//var data1 = {jack: "ok"};
			$.each(data, function(index, value){
				DebugOutput( index + ":" + value );
			});
			
		}, "json");
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
		
		$( "#tabs" ).tabs();
		
		$( "#search" ).button({
            text: false,
            icons: {
                primary: "ui-icon-search"
            }
        }).click(SubmitSearchFrom);
	});
	
	</script>
	<style>

	</style>
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

<div id="tabs">
    <ul>
        <li><a href="#tabs-1">Search and Modify</a></li>
        <li><a href="#tabs-2">Insert New Record</a></li>
        <li><a href="#tabs-3">Temp</a></li>
    </ul>
    <div id="tabs-1">
        <p>Search Form</p>
		<form id="searchFrom">
		<fieldset>
			<label for="drawingNo">Drawing No.</label>
			<input type="text" name="drawingNo" id="drawingNo" class="text ui-widget-content ui-corner-all" />
			<label for="description">Description</label>
			<input type="password" name="description" id="description" value="" class="text ui-widget-content ui-corner-all" />
			<label for="date">Revision Date (after)</label>
			<input type="password" name="revisionDate" id="revisionDate" value="" class="text ui-widget-content ui-corner-all" />
			<input type="hidden" name="op" id="dateOperation" value=">"></input>
			<button id="search">Search</button>
		</fieldset>
		</form>
    </div>
    <div id="tabs-2">
        <p>Insert From (not ready)</p>
    </div>
    <div id="tabs-3">
        <p>Temp page.</p>
    </div>
</div>
</body>