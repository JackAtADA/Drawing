function InitRecordDialog(){
	$( "#fileReplaceOptionSelected" ).val("keepOriginalFile");
	$( "#fileReplaceOption1").attr("checked","checked");
	//$( "#fileReplaceOption2").removeAttr("checked");
	//$( "#fileReplaceOption3").removeAttr("checked");
	$( "#updateRevisionButton" ).button()
		.click(function (event){
			event.preventDefault();
			SubmitUpdateForm();
		});
	
	$( "#dateResult" ).datepicker({ dateFormat: "yy-mm-dd" });
	$( "#fileReplaceOption" ).buttonset();
	
	$( "#fileReplaceOption1" ).click( function (){
		ResetUpdateRevisionButton();
		$( "#fileReplaceOptionSelected" ).val("keepOriginalFile");
	});
	$( "#fileReplaceOption2" ).click( function (){
		ResetUpdateRevisionButton();
		$( "#fileReplaceOptionSelected" ).val("removeOldFile");
	});
	
	$('#fileReplaceScope').hide();
	$( "#fileReplaceOption3" ).click(function (){
		$( "#fileReplaceOptionSelected" ).val("replaceWithFile");
		$('#fileReplaceScope').show();
		$('#fileUploadReplace').fileupload({
			dataType: 'json',
			singleFileUploads: false, 
			add: function (e, data) {
				//DebugOutput(data);
				$("#updateRevisionButton").unbind('click');
				$( "#fileNameReplace" ).val(data.files[0].name);
				$( "#updateRevisionButton" ).click(function (event){
					event.preventDefault();
					data.submit();
				});
				$('#progressReplace').css(
					'width', '0%'
				);
			},
			done: function (e, data) {
				DebugOutput("file sent done");
				$( "#fileReplaceOptionSelected" ).val("replaceWithFile");
				SubmitUpdateForm();
			},
			progressall: function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
				DebugOutput("progress:" + progress);
				$('#progressReplace').css(
					'width',
					progress + '%'
				);
			}
		});
	});
}

function ResetUpdateRevisionButton(){
	// Init/reset - unbind and re-bind
	$("#fileReplaceScope").hide();
	$("#updateRevisionButton").unbind('click');
	$( "#updateRevisionButton" ).click(function (event){
		event.preventDefault();
		SubmitUpdateForm();
	});
	// Reset the file name to empty string
	$( "#fileNameReplace" ).val("");
}

function SubmitUpdateForm(){
	var updateType = "drawingRevision";
	var drawingNo = "";
	updateField = {
		"updateType": updateType,
		"drawingNo": $( "#referenceDrawingNoResult" ).val(), 
		"revisionNo": $( "#revisionNoResult" ).val(),
		"date": $( "#dateResult").val(),
		"workOrder": $("#workOrderResult").val(),
		"followUp": $("#followUpResult").val(),
		"typeName": $("#typeNameResult").val(),
		"fileName" : $("#fileNameReplace").val(),
		"fileReplaceOption" : $( "#fileReplaceOptionSelected" ).val(),
		"recordID" : $("#recordIDResult").val(),
		"op" : "update",
	};
	$.get("Controller/updateHandler.php", updateField, function(data){
		$.each(data, function(index, value){
			DebugOutput(index + ":" + value);
		});
		if (data.ret == 1){
			// tips with update success
			UpdateTips("#recordDialogTips", "Update Successful");
		}else {
			// tips with error message
			UpdateTips("#recordDialogTips", data.error);
		}
	}, "json");
}