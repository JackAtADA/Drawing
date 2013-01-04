function SubmitInsertForm(e){
	e.preventDefault();
	
	/*
	var drawingNo = $( "#drawingNoNew" );
	var insertType = $('input[name="insertType"]:checked').val();
	var description;
	if (insertType == "drawing"){
		description = $( "#descriptionNew" );
	}
	
	searchField = {
		"drawingNo": drawingNo.val(), 
		"description": description.val(),
		"date": $( "#dateNew").val(),
		"fileLocation": $( "#fileLocationNew" ).val(),
		"typeName": $("#typeNameNew").val(),
		"workOrder": $("#workOrderNew").val(),
		"followUp": $("#followUpNew").val(),
		"op" : "insert"
	};
	
	$.get("Controller/updateHandler.php", updateField, function(data){
		
		
	}, "json");
	*/
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
	$( "#submitNew").button().click(SubmitInsertForm);
	
	
}
