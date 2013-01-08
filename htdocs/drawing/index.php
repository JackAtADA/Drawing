<?php ?>

<html lang="us">
<head>
	<meta charset="utf-8">
	<title>Drawing DateBase</title>
	<link href="css/smoothness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
	<link href="css/main.css" />
	<script src="js/jquery-1.8.3.js"></script>
	<script src="js/jquery-ui-1.9.2.custom.js"></script>
	<script src="js/login.js"></script>
	<script src="js/main.js"></script>
	<script src="js/insert.js"></script>
	<script>
	// js UI
	function ColoringAllJTable(){
		$(".jtable th").each(function(){
			$(this).addClass("ui-widget-header");
		});
		$(".jtable td").each(function(){
			$(this).addClass("ui-widget-content");
		});
		$(".jtable tr").hover(
			function()
			{
				$(this).children("td").addClass("ui-state-hover");
			},
			function()
			{
				$(this).children("td").removeClass("ui-state-hover");
			}
		);
		$(".jtable tr").click(function(){
			$(this).children("td").toggleClass("ui-state-highlight");
		});
	}
	function DialogSubmit(userName, password) {
		LoginRequest(userName, password, 
			function(){$("#dialog-form").dialog("close")},
			UpdateTips
		);
	}
	function InitResultButton( recordID ){
		$( "#record" + recordID ).button({
            text: false,
            icons: {
                primary: "ui-icon-newwin"
            }
        }).click( function(){
			SearchDetailRecordInfo(recordID);
		});
	}
	function SearchDetailRecordInfo( recordID ){
		//alert("still in development");
		var searchField = {
			"recordID": recordID, 
			"search": "specific"
		};
		$.get("Controller/searchHandler.php", searchField, function(data){
			if ( data.ret == "1"){ // success
				$( "#recordDialog" ).dialog( "open" );
				if ( typeof data.rowResult[0] == "undefined"){
					return;
				}
				var record = data.rowResult[0];
				$( "#drawingNoResult" ).val( record["DrawingNo"] );
				$( "#descriptionResult" ).val( record["Description"] );
				$( "#referenceDrawingNoResult" ).val( record["DrawingNo"] );
				$( "#revisionNoResult" ).val( record["RevisionNo"] ); 
				$( "#typeNameResult" ).val( record["TypeName"] );
				$( "#fileLocationResult" ).val( record["FileLocation"] );
				$( "#dateResult" ).val( record["Date"] );
				$( "#workOrderResult" ).val( record["WorkOrder"] );
				$( "#followUpResult" ).val( record["FollowUp"] );
			}else{
				alert(data.error);
				if (data.error == "Permission deny, user has not login"){
					$( "#dialog-form" ).dialog( "open" );
				}
			}
		}, "json");
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
			
			var searchResult = $( "#searchResult" );
			searchResult.empty();
			if ( data.ret == "1"){ // success
				var num = 0;
				var itemTable = searchResult.append("<table></table>").find("table");
				//itemTable.addClass("ui-widget");
				//itemTable.addClass("resultTable");
				itemTable.addClass("jtable");
				//itemTable.append("<tr class='ui-widget-header'><th></th><th>Drawing No</th><th>Description</th><th>Revision Date</th><th>Revision Number</th><th>File Location</th></tr>");
				itemTable.append("<tr><th></th><th>Drawing No</th><th>Description</th><th>Revision Date</th><th>Revision Number</th><th>File Location</th></tr>");
				$.each(data.rowResult, function(index, record){
					num++;
					//itemTable.append("<tr class='ui-widget-content'></tr>");
					//itemTable.append("<tr class='resultTable'></tr>");
					itemTable.append("<tr></tr>");
					var item = itemTable.find("tr").last();
					//item.addClass("ui-widget-content");
					$.each(record, function(indexN, value){
						if (indexN == "RecordID"){
							item.append("<td><button id='record" + record["RecordID"] + "'></button></td>");
						}else if (indexN == "TypeName"){
							// skip
						}else if (indexN == "FileLocation"){
							item.append("<td><a href='" + value + "'>" + value + "</a></td>");
						}else{
							//item.append("<td class='resultTable'>" + value + "</td>");
							item.append("<td>" + value + "</td>");
						}
					});
					InitResultButton( record["RecordID"]  );
				});
				ColoringAllJTable();
				$("#numOfResult").html("Search Ruselts:" + num);
			}else{
				if (data.error == "Permission deny, user has not login"){
					alert(data.error);
					$( "#dialog-form" ).dialog( "open" );
				}
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
			width: 500,
			modal: true,
			buttons: {
				/*
				"Update(not ready)": function() { 
				},
				"Cancel": function() {
					$( this ).dialog( "close" );
				}*/
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
		
		InitInsertTab();
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

<!--
<div id="testDIV">
	<form>
	<fieldset>
	<input type="text" name="test" id="test" value="testString" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	</form>
</div>
-->

<div id="recordDialog" title="Revision Record">
    <form>
    <fieldset>
		<table class="ui-widget">
		<tr>
			<td><label for="drawingNoResult">DrawingNo</label></td>
			<td><input type="text" name="drawingNoResult" id="drawingNoResult" disabled class="text ui-widget-content ui-corner-all ui-state-disabled"/></td>
		</tr>
        <tr>
			<td><label for="descriptionResult">Description</label></td>
			<td><textarea name="descriptionResult" id="descriptionResult" rows="5" cols="30" maxlength="1023" class="text ui-widget-content ui-corner-all ui-state-disabled" disabled></textarea></td>
		</tr>
		<tr><td colspan="2"  align="right"><button id="modifyDrawingRecordButton">Modify the Drawing Record(not ready)</button></td></tr>
		<tr><td colspan="2"><hr/></td></tr>
		<tr>
			<td><label for="referenceDrawingNoResult">Reference to DrawingNo</label></td>
			<td><input type="text" name="referenceDrawingNoResult" id="referenceDrawingNoResult" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="revisionNoResult">RevisionNo</label></td>
        	<td><input type="text" name="revisionNoResult" id="revisionNoResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="dateResult">Date</label></td>
        	<td><input type="text" name="dateResult" id="dateResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="fileLocationResult">FileLocation</label></td>
        	<td><input type="text" name="fileLocationResult" id="fileLocationResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="typeNameResult">FileType</label></td>
			<td><input type="text" name="typeNameResult" id="typeNameResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="workOrderResult">WorkOrder</label></td>
			<td><input type="text" name="workOrderResult" id="workOrderResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="followUpResult">FollowUp</label></td>
			<td><input type="text" name="followUpResult" id="followUpResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr><td colspan="2" align="right"><button id="updateRevisionButton">Update Revision(not ready)</button></td></tr>
		</table>
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
			<input type="text" name="description" id="description" value="" class="text ui-widget-content ui-corner-all" />
			<label for="date">Revision Date (after)</label>
			<input type="text" name="revisionDate" id="revisionDate" value="" class="text ui-widget-content ui-corner-all" />
			<input type="hidden" name="op" id="dateOperation" value=">="></input>
			<button id="search">Search</button>
		</fieldset>
		</form>
		<p id="numOfResult"></p>
		<div id="searchResult">
		</div>
    </div>
    <div id="tabs-2">
        <p>Insert From (not ready)</p>
		<!-- the controll js for this tabs is in js/insert.js -->
		<div id="recordInsert" title="Record">
			<form>
			<fieldset>
				<table class="ui-widget">
				<tr>
					<td colspan="2">
					<div id="newRecordRadio">
						<input type="radio" id="radio1" name="insertType" value="drawing"/><label for="radio1">New Drawing</label>
						<input type="radio" id="radio2" name="insertType" value="revision" checked="checked" /><label for="radio2">New Revision</label>
					</div>
					</td>
				</tr>
				<tr>
					<td><label for="drawingNoNew">DrawingNo</label></td>
					<td><input type="text" name="drawingNoNew" id="drawingNoNew" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td><label for="descriptionNew">Description</label></td>
					<td><textarea name="descriptionNew" id="descriptionNew" rows="5" cols="30" maxlength="1023" class="text ui-widget-content ui-corner-all ui-state-disabled"></textarea></td>
				</tr>
				<tr>
					<td><label for="revisionNoNew">RevisionNo</label></td>
					<td><input type="text" name="revisionNoNew" id="revisionNoNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td><label for="dateNew">Date</label></td>
					<td><input type="text" name="dateNew" id="dateNew" value="" class="text ui-widget-content ui-corner-all" size="30"/></td>
				</tr>
				<tr>
					<td><label for="fileLocationNew">FileLocation</label></td>
					<td><input type="text" name="fileLocationNew" id="fileLocationNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="1023"/></td>
				</tr>
				<tr>
					<td><label for="typeNameNew">FileType</label></td>
					<td><input type="text" name="typeNameNew" id="typeNameNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td><label for="workOrderNew">WorkOrder</label></td>
					<td><input type="text" name="workOrderNew" id="workOrderNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td><label for="followUpNew">FollowUp</label></td>
					<td><input type="text" name="followUpNew" id="followUpNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr><td colspan="2" align="right"><button id="submitNew">New</button></td></tr>
				</table>
			</fieldset>
			</form>
		</div>
    </div>
    <div id="tabs-3">
        <p>Temp page.</p>
    </div>
</div>



</body>
