/* drawing database utility
 * developed by jack
 */

function DebugOutput(data){
	if ($.browser.chrome){
		console.log(data);
	}
}

function UpdateTips( selector, message ) {
	var tips = $( selector );
	tips.text( message );
	//DebugOutput(message);
	tips.addClass( "ui-state-highlight" );
	
	setTimeout(function() {
		tips.removeClass( "ui-state-highlight", 1500 );
	}, 500 );
	
}