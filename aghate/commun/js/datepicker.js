	$(function() {
		$("#date_deb").datepicker({ dateFormat: "dd/mm/yy" });
	});
	
	$(function() {
		$( "#datepicker","#date_deb","#date_fin" ).datepicker({ dateFormat: "dd/mm/yy" });
	});

	$(function() {
		$("#date_deb","#date_fin" ).datepicker({ dateFormat: "dd/mm/yy" });
	});

