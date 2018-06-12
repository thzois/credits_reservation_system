
$(document).ready(function () {
	
	//disable typing in date picker input
	$('#dpd1,#dpd2,#dpdFlight1,#dpdFlight2,#start_date,#end_date').keydown(function(e) {
		 e.preventDefault();
		 return false;
	});
	//disable typing in date picker input end

	//main date picker
	var nowTemp = new Date();
	var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
	var checkin = $('#dpd1,#dpdFlight1').datepicker({
		format: 'dd-mm-yyyy',
		startDate: now,
		autoclose: true,
		orientation: 'top left'
	})
	.on('changeDate', function(e){	
		selStartDate = e.date;
		var nextDay = new Date(e.date);
		nextDay.setDate(nextDay.getDate() + 1);
		$('#dpd2,#dpdFlight2').datepicker('setStartDate', nextDay);
		if(checkout.val() == '') checkout.focus();	

		if (checkout.datepicker('getDate') == 'Invalid Date') {
			var newDate = new Date(e.date)
			newDate.setDate(newDate.getDate() + 1);
			checkout.datepicker('update',newDate);
			checkout.focus();	
		}

	});	    

	var checkout = $('#dpd2,#dpdFlight2').datepicker({
		format: 'dd-mm-yyyy',
		startDate: now,
		autoclose: true,
		orientation: 'top'
	})
	.on('changeDate', function(e){					
	});	
	//main date picker end

});