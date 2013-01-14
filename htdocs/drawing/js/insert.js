function SubmitInsertForm(e){
	e.preventDefault();
	
	var insertType = $('input[name="insertType"]:checked').val();
	
	updateField = {
		"insertType": insertType,
		"drawingNo": $( "#drawingNoNew" ).val(), 
		"description": $( "#descriptionNew" ).val(),
		"revisionNo": $("#revisionNoNew").val(),
		"date": $( "#dateNew").val(),
		"fileLocation": $( "#fileLocationNew" ).val(),
		"typeName": $("#typeNameNew").val(),
		"workOrder": $("#workOrderNew").val(),
		"followUp": $("#followUpNew").val(),
		"op" : "insert"
	};
	
	$.get("Controller/updateHandler.php", updateField, function(data){
		//$.each(data, function (index, value){
			//DebugOutput(index + ":" + value);
		//});
		if (data.ret == 1){
			alert("insert successful");
			var IDs = [ 
				"#drawingNoNew",
				"#descriptionNew",
				"#revisionNoNew",
				"#dateNew",
				"#fileLocationNew",
				"#typeNameNew",
				"#workOrderNew",
				"#followUpNew"
			];
			ClearValues(IDs);
		}else{
			alert(data.error);
			if (data.error == "Permission deny, user has not login"){
				$( "#dialog-form" ).dialog( "open" );
			}
		}
	}, "json");
}

function ClearValues( IDs ) {
	$.each( IDs, function (index, ID){
		DebugOutput(ID + ":" + $( ID ).val());
		$( ID ).val("");
	});
}

function ChangeStateOfInsertForm(newDrawing){
	if (newDrawing == true){
		$( "#descriptionNew" ).removeClass("ui-state-disabled");
		$( "#descriptionNew" ).removeAttr("disabled");
	}else{
		$( "#descriptionNew" ).addClass("ui-state-disabled");
		$( "#descriptionNew" ).attr("disabled", true);
	}
}

function InitInsertTab(){
	$( "#newRecordRadio" ).buttonset();

	$( "#radio1" ).click(function(){
		ChangeStateOfInsertForm(true);
	});
	$( "#radio2" ).click(function(){
		ChangeStateOfInsertForm(false);
	});
	$( "#submitNew" ).button().click(SubmitInsertForm);
	$( "#dateNew" ).datepicker({ dateFormat: "yy-mm-dd" });
	
	
}
