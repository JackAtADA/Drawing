function InitRecordDialog(){
	$( "#updateRevisionButton" ).button()
		.click(function (event){
			event.preventDefault();
			//SubmitInsertForm();
		});
	
	$( "#dateResult" ).datepicker({ dateFormat: "yy-mm-dd" });
	
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
			DebugOutput("done");
			//SubmitInsertForm();
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
}
