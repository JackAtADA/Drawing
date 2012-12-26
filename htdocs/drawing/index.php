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
			var searchResult = $( "#searchResult" );
			if ( data.ret == "1"){ // success
				var num = 0;
				//var listItem = searchResult.append("<ol></ol>");
				var itemTable = searchResult.append("<table></table>").find("table");
				itemTable.addClass("ui-widget");
				itemTable.append("<tr class='ui-widget-header'><th>Drawing No</th><th>Description</th><th>Revision Date</th><th>Revision Number</th><th>File Location</th></tr>");
				$.each(data.rowResult, function(index, record){
					num++;
					itemTable.append("<tr class='ui-widget-content'></tr>");
					var item = itemTable.find("tr").last();
					//item.addClass("ui-widget-content");
					$.each(record, function(indexN, value){
						item.append("<td>" + value + "</td>");
						DebugOutput( indexN + ":" + value );
					});
				});
				$("#numOfResult").append("Search Ruselts:" + num);
			}
			
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
		
		$( "#recordDialog" ).dialog({
			autoOpen: false,
			//height: 350,
			//width: 400,
			modal: true,
			buttons: {
				"Update": function() { 
				},
				"Cancel": function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				
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

<div id="recordDialog" title="Record">
	<p id="loginTips"></p>
    <form>
    <fieldset>
        <label for="drawingNo">DrawingNo</label>
        <input type="text" name="drawingNo" id="drawingNo" class="text ui-widget-content ui-corner-all" />
        <label for="description">Description</label>
        <input type="text" name="description" id="description" value="" class="text ui-widget-content ui-corner-all" />
		<label for="revisionNo">RevisionNo</label>
        <input type="text" name="revisionNo" id="revisionNo" value="" class="text ui-widget-content ui-corner-all" />
		<label for="date">Date</label>
        <input type="text" name="date" id="date" value="" class="text ui-widget-content ui-corner-all" />
		<label for="fileLocation">FileLocation</label>
        <input type="text" name="fileLocation" id="fileLocation" value="" class="text ui-widget-content ui-corner-all" />
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
		<div id="searchResult">
			<p id="numOfResult"></p>
		</div>
    </div>
    <div id="tabs-2">
        <p>Insert From (not ready)</p>
    </div>
    <div id="tabs-3">
        <p>Temp page.</p>
    </div>
</div>



</body>