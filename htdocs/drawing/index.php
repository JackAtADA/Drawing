<?php ?>
<!DOCTYPE HTML>
<html lang="us">
<head>
	<meta charset="utf-8">
	<title>Drawing DateBase</title>
	<link href="css/smoothness/jquery-ui-1.9.2.custom.css" rel="stylesheet" />
	<link href="css/main.css" rel="stylesheet" />
	<link href="css/jquery.fileupload-ui.css" rel="stylesheet" />
	<script src="js/jquery-1.8.3.js"></script>
	<script src="js/jquery-ui-1.9.2.custom.js"></script>
	<script src="js/login.js"></script>
	<script src="js/main.js"></script>
	<script src="js/insert.js"></script>
	<script src="js/search.js"></script>
	<script src="js/update.js"></script>
	<script src="js/vendor/jquery.ui.widget.js"></script>
	<script src="js/jquery.iframe-transport.js"></script>
	<script src="js/jquery.fileupload.js"></script>
	<script src="js/jquery.fileupload-ui.js"></script>
	<script>
	// js UI
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

<div id="recordDialog" title="Revision Record">
    <form>
    <fieldset>
		<table class="ui-widget">
		<tr>
			<td><label for="drawingNoResult">Drawing No.</label></td>
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
			<td><label for="revisionNoResult">Revision No.</label></td>
        	<td><input type="text" name="revisionNoResult" id="revisionNoResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="dateResult">Date</label></td>
        	<td><input type="text" name="dateResult" id="dateResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<!--
		<tr>
			<td><label for="fileLocationResult">FileLocation</label></td>
        	<td><input type="text" name="fileLocationResult" id="fileLocationResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		!-->
		<tr>
			<td><label for="workOrderResult">WorkOrder</label></td>
			<td><input type="text" name="workOrderResult" id="workOrderResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="followUpResult">FollowUp</label></td>
			<td><input type="text" name="followUpResult" id="followUpResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="typeNameResult">FileType</label></td>
			<td><input type="text" name="typeNameResult" id="typeNameResult" value="" class="text ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="fileLocationResult">File</label></td>
        	<td><a href="" id="fileLocationResult">link</a></td>
		</tr>
		<tr>
			<td>
				<span class="fileinput-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
					<span class="ui-button-text" style="padding:.4em 1em;">Replace file</span>
					<!-- <span class="ui-button-text-only ui-button-text">Replace file</span> -->
					<input id="fileUploadReplace" type="file" name="files[]" data-url="Controller/Uploads/" />
				</span>
			</td>
			<td><input type="text" id="fileNameReplace" value="" disabled class="text ui-widget-content ui-corner-all ui-state-disabled" size="30" maxlength="254"/></td>
		</tr>
		<tr>
			<td colspan="2"><div id="progressReplace" class="bar" style="width: 0%;"></div></td>
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
        <p>Insert From</p>
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
					<td><label for="drawingNoNew">Drawing No.</label></td>
					<td><input type="text" name="drawingNoNew" id="drawingNoNew" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td><label for="descriptionNew">Description</label></td>
					<td><textarea name="descriptionNew" id="descriptionNew" rows="5" cols="30" maxlength="1023" disabled class="text ui-widget-content ui-corner-all ui-state-disabled"></textarea></td>
				</tr>
				<tr>
					<td><label for="revisionNoNew">Revision No.</label></td>
					<td><input type="text" name="revisionNoNew" id="revisionNoNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td><label for="dateNew">Date</label></td>
					<td><input type="text" name="dateNew" id="dateNew" value="" class="text ui-widget-content ui-corner-all" size="30"/></td>
				</tr>
				<tr>
					<td><label for="workOrderNew">WorkOrder</label></td>
					<td><input type="text" name="workOrderNew" id="workOrderNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td><label for="followUpNew">FollowUp</label></td>
					<td><input type="text" name="followUpNew" id="followUpNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<!--
				<tr>
					<td><label for="fileLocationNew">FileLocation</label></td>
					<td><input type="text" name="fileLocationNew" id="fileLocationNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="1023"/></td>
				</tr>
				-->
				<tr>
					<td><label for="typeNameNew">FileType</label></td>
					<td><input type="text" name="typeNameNew" id="typeNameNew" value="" class="text ui-widget-content ui-corner-all" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td>
						<span class="fileinput-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
							<span class="ui-button-text">Add files...</span>
							<input id="fileupload" type="file" name="files[]" data-url="Controller/Uploads/" />
						</span>
					</td>
					<td><input type="text" id="fileNameNew" value="" disabled class="text ui-widget-content ui-corner-all ui-state-disabled" size="30" maxlength="254"/></td>
				</tr>
				<tr>
					<td colspan="2"><div id="progress" class="bar" style="width: 0%;"></div></td>
				</tr>
				<tr>
					<td>
					</td>
					<td align="right"><button id="submitNew">New</button></td>
				</tr>
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
