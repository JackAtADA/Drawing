function SubmitInsertForm(){
	var insertType = $('input[name="insertType"]:checked').val();
	
	updateField = {
		"insertType": insertType,
		"drawingNo": $( "#drawingNoNew" ).val(), 
		"description": $( "#descriptionNew" ).val(),
		"revisionNo": $("#revisionNoNew").val(),
		"date": $( "#dateNew").val(),
		//"fileLocation": $( "#fileLocationNew" ).val(),
		"typeName": $("#typeNameNew").val(),
		"workOrder": $("#workOrderNew").val(),
		"followUp": $("#followUpNew").val(),
		"fileName" : $("#fileNameNew").val(),
		"op" : "insert"
	};
	
	$.each(updateField, function (index, value){
		DebugOutput(index + ":" + value);
	});
	$.get("Controller/updateHandler.php", updateField, function(data){
		/*
		$.each(data, function (index, value){
			DebugOutput(index + ":" + value);
		});
		*/
		if (data.ret == 1){
			alert("insert successful");
			var IDs = [ 
				"#drawingNoNew",
				"#descriptionNew",
				"#revisionNoNew",
				"#dateNew",
				//"#fileLocationNew",
				"#typeNameNew",
				"#workOrderNew",
				"#followUpNew",
				"#fileNameNew",
			];
			ClearValues(IDs);
			$('#progress').css(
				'width',
				'0%'
			);
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
	$( "#submitNew" ).button();
	$( "#dateNew" ).datepicker({ dateFormat: "yy-mm-dd" });
		
	$('#fileupload').fileupload({
		dataType: 'json',
		singleFileUploads: false, 
		add: function (e, data) {
			//DebugOutput(data);
			$("#submitNew").unbind('click');
			$( "#fileNameNew" ).val(data.files[0].name);
			$( "#submitNew" ).click(function (event){
				event.preventDefault();
				data.submit();
			});
			$('#progress').css(
				'width', '0%'
			);
		},
		done: function (e, data) {
			DebugOutput("done");
			SubmitInsertForm();
			/*
			$.each(data.result.files, function (index, file) {
				
				$('<p/>').text(file.name).appendTo(document.body);
			});
			*/
		},
		progressall: function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			DebugOutput("progress:" + progress);
			$('#progress').css(
				'width',
				progress + '%'
			);
		}
	});
	
}
