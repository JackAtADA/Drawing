function SearchDetailRecordInfo( recordID ){
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
			itemTable.append("<tr><th></th><th>Drawing No.</th><th>Description</th><th>Revision Date</th><th>Revision Number</th><th>File Location</th></tr>");
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
	
	//$(".jtable tr").click(function(){
		//$(this).children("td").toggleClass("ui-state-highlight");
	//});
}
