events_array = [];

function clearResForm(){
    $('#title').val('');
    $('#startDate').datepicker('setDate', null);
    $('#endDate').datepicker('setDate', null);
    
    $('#startTime').val('');
    $('#endTime').val('');

    $('.gpuMachine').hide();
    $(".modal-title").html("New Reservation");
    $('.save-button').removeAttr('id');
    $("input:checkbox[name='gpusNeed']").prop("checked", false);
    $("input:checkbox[name='machine_name[]']").prop("checked", false);
    $("input:checkbox[exclusive-check='exclusive-check']").prop("checked", false);
}



function cancelReservation(reservationID){
    var result = confirm("Are you sure that do you want to cancel this reservation? Exclusive reservations must be canceled at least 1 day before the actual date to get your credits back!");
    if(result){
        $.ajax({
                type            : 'POST',
                url             : 'calendar.php',
                data            : { "cancelReservation" : reservationID},
                success         : function(res, status) {
                    if(res === "reservationCancelled"){
                        alert("Reservation cancelled successfully!");
                        window.location.reload();
                    }else{
                        alert(res);
                    }
                },
                error           : function(xhr, desc, err) {
                    console.log(xhr);
                    console.log("Details: " + desc + "\nError:" + err);
                }
        })
    }
}



function updateRes(resID, formData, eventDrop, revertFunc, confirmCase){
    
    formData = formData.replace("editReservation=true&", "");

    if(confirmCase === "updateNoRefund"){
        var sendUpdate = "confirmUpdate=true&resID="+resID+"&"+formData;
    }else{
        var sendUpdate = "confirmConsume=true&resID="+resID+"&"+formData;
    }
    
    $.ajax({
        type        : 'POST',
          url       : 'calendar.php',
          data	 	: sendUpdate,
          success   : function(res, status) {
                if(res === "updated"){
                    alert("Your reservation updated successfully!");
                    window.location.reload();
                }else{
                    alert(res);
                    if(eventDrop==="true"){
                        revertFunc();
                    }
                }
          },
          error           : function(xhr, desc, err) {
            console.log(xhr);
            console.log("Details: " + desc + "\nError:" + err);
          }
    }) 
}



function reservationRequest(formData, resID, eventDrop, revertFunc){
    $.ajax({
          type      : 'POST',
          url       : 'calendar.php',
          data	 	: formData,
          success   : function(res, status) {
                if(res === "success"){
                    alert("Reservation successfull!");
                    window.location.reload();
                }else if(res === "failed"){
                    alert("Not enough credits!");
                    if(eventDrop==="true"){
                        revertFunc();
                    }
                }else if(res === "conflict"){
                    alert("There is a conflict with your reservation, make changes and try again!");
                    if(eventDrop==="true"){
                        revertFunc();
                    }
                }else if(res === "updated"){
                    alert("Your reservation updated successfully!");
                    window.location.reload();
                }else if(res === "edit_but_no_refund"){
                    var result = confirm("You won't get a refund of your credits because you are changing an exclusive reservation one day before the actual date. Are you sure that you still want to make the reservation non exclusive?");

                    var confirmCase = "updateNoRefund";
                    
                    if(result){
                        /* Send request to update res without refund! */
                        updateRes(resID, formData, eventDrop, revertFunc, confirmCase);
                    }else{
                        if(eventDrop==="true"){
                            revertFunc();
                        }else{
                            $("input:checkbox[exclusive-check='exclusive-check']").prop("checked", true);
                        }
                    }
                }else if(res === "edit_consume_again"){
                    var result = confirm("The system will consume credits again for this reservation. Are you sure that you want to proceed?");
                    
                    var confirmCase = "consumeAgain";
                    
                    if(result){
                        /* Send request to confirm the reservation with consumed credits again! */
                        updateRes(resID, formData, eventDrop, revertFunc, confirmCase);
                    }else{
                        if(eventDrop==="true"){
                            revertFunc();
                        }else{
                            $("input:checkbox[exclusive-check='exclusive-check']").prop("checked", true);
                        }
                    }
                }else{
                    alert(res);
                    if(eventDrop==="true"){
                        revertFunc();
                    }
                }
          },
          error           : function(xhr, desc, err) {
            console.log(xhr);
            console.log("Details: " + desc + "\nError:" + err);
          }
    }) 
}



function checkForm(resID){
    /* Check form is valid */
    if($('form')[0].checkValidity()){
        /* Check if there are any selected machines */
        var checked = $("input:checkbox[name='machine_name[]']:checked").length;
        if(checked === 0){
            alert("You must select at least one machine!");
        }else{
            /* Proceed with the reservation */
            if(resID){
                formData = "editReservation=true&resID=" + resID + "&" + $("form").serialize();
            }else{
                formData = "reservation=true&" + $("form").serialize();
            }

            reservationRequest(formData, resID, "false");
        }
    }
}



function eventClick(event){
    if(event.currUserEvent === "true"){
        $(".modal-title").html("Edit Reservation");
        $(".modal-title").append("<button type='button' class='btn delete-res btn-danger' style='margin-left:20px; top:-2px; position:relative;' onclick='cancelReservation(this.id)' id="+event.id+">Delete</button>");

        $(".save-button").attr('id', event.id);

        $("#title").val(event.onlyTitle);

        if(event.exclusive === '1'){
             $("input:checkbox[exclusive-check='exclusive-check']").prop("checked", true);
        }

        var startDate = (event.d1).split("-");
        var endDate = (event.d2).split("-");
        
        $("#startDate").datepicker('setDate', (startDate[2]+"/"+startDate[1]+"/"+startDate[0]));
        $("#endDate").datepicker('setDate', (endDate[2]+"/"+endDate[1]+"/"+endDate[0]));

        startDate = (startDate[2]+"/"+startDate[1]+"/"+startDate[0]);
        endDate = (endDate[2]+"/"+endDate[1]+"/"+endDate[0]);
        var startTime = event.t1;
        var endTime = event.t2;
        
        $("#startTime").val(event.t1);
        $("#endTime").val(event.t2);

        /* Check machines checkboxes */
        $("input:checkbox[name='machine_name[]']").prop("checked", false);

        var pcNames = (event.machines).split(" ");

        for(var i=0; i<pcNames.length; i++){
            $("input:checkbox[ics-name='"+ pcNames[i] +"']").prop("checked", true);
        }
        
        $(".questionGPU").hide();
        $(".gpuMachine").show();

        getFreeMachines(startDate, endDate, startTime, endTime);
        
        $("#myModal").modal();
    }else{
        var excl = "<strong class='text-primary'>NOT Exclusive!</strong>";
        if(event.exclusive === '1'){
            var excl = "<strong class='text-danger'>Exclusive!</strong>";
        }
        $("#eventInfo").replaceWith('<div class="modal fade" id="eventInfo" role="dialog"><div class="modal-dialog modal-md"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">'+event.onlyTitle+' - '+excl+'</h4></div><div class="modal-body"><p><strong>Who:</strong> '+event.username+'</p><p><strong>From: </strong> ' + event.start.format("DD/MM/YYYY - HH:mm") + '</p><p><strong>To: </strong> ' + event.end.format("DD/MM/YYYY - HH:mm")+'</p><p><strong>Reserved:</strong> '+event.machines+'</p></div><div class="modal-footer"><button type="button" class="btn btn-primary btn-danger" data-dismiss="modal">Close</button></div></div></div></div>');
        $("#eventInfo").modal();
    }
}



function dateTimeClick(start_date, end_date){

    var today = new Date().toISOString().split('T')[0];

    if(start_date.format("YYYY-MM-DD") < today){
        return;
    }
    
    var dateTime = (start_date.format("DD/M/YYYY HH:mm:ss")).split(" ");
    var startDate = dateTime[0];
    var startTime = dateTime[1];

    dateTime = (end_date.format("DD/M/YYYY HH:mm:ss")).split(" ");
    var endDate = dateTime[0];
    var endTime = dateTime[1];

    //  $('#modalForm').click();
    $("#myModal").modal();
    
    $(".questionGPU").show();

    clearResForm();

    if(typeof startTime != 'undefined'){
        $('#startTime').val(startTime);    
    }else{
        $('#startTime').val('');    
    }

    $('#startDate').datepicker('setDate', startDate);
    $('#endDate').datepicker('setStartDate', startDate);


    if(typeof endTime != 'undefined'){
        $('#endTime').val(endTime);    
    }else{
        $('#endTime').val('');    
    }

    $('#endDate').datepicker('setDate', endDate);
    $('#startDate').datepicker('setEndDate', endDate);
}



function eventResizeDrop(event, delta, revertFunc){
    var startDateTime;
    var endDateTime;
    var today = new Date().toISOString().split('T')[0];
    
    if((event.start.format()).includes("T")){
        startDateTime = (event.start.format()).split("T");
    }else{
        startDateTime = [event.start.format(), "00:00:00"];
    }

    var splitStartDate = startDateTime[0].split("-");

    if(event.end){
        if((event.end.format()).includes("T")){
            endDateTime = (event.end.format()).split("T");
        }else{
            endDateTime = [event.end.format(), "00:00:00"];
        }
    }else{
        if(event.allDay === true){
            var endAllDate = splitStartDate[0]+"-"+splitStartDate[1]+"-"+(parseInt(splitStartDate[2])+1).toString();    
            endDateTime = [endAllDate, "00:00:00"];
        }else{
            var endDate = splitStartDate[0]+"-"+splitStartDate[1]+"-"+(parseInt(splitStartDate[2])).toString();    
            
            var timeToMs = new Date(delta._milliseconds).toTimeString();
            var endTime = timeToMs.split(" ");
            endDateTime = [endDate, endTime[0]]; 
        }
    }

    var machines = (event.machines_numbers).split(" ");
    machines.map(Function.prototype.call, String.prototype.trim);

    
    if(startDateTime[0] < today){
        revertFunc();
    }else{   
        /* Format start date */
        startDateTime[0] = startDateTime[0].replace(/-/g, "/");
        tmp = startDateTime[0].split("/"); 
        startDateTime[0] = tmp[2]+"/"+tmp[1]+"/"+tmp[0];

        /* Format end date */
        endDateTime[0] = endDateTime[0].replace(/-/g, "/");
        tmp = endDateTime[0].split("/"); 
        endDateTime[0] = tmp[2]+"/"+tmp[1]+"/"+tmp[0];

        var formData = "editReservation=true&resID="+event.id+"&title="+event.onlyTitle+"&startDate="+startDateTime[0]+"&startTime="+startDateTime[1]+"&exclusive="+event.exclusive+"&endDate="+endDateTime[0]+"&endTime="+endDateTime[1];

        for(var i=0; i<machines.length; i++){
            formData+="&machine_name[]="+machines[i];
        }

        if(confirm("Do you want to save changes?") === true){
            reservationRequest(formData, event.id, "true", revertFunc);    
        }else{
            revertFunc();
        }
    }
}



function colorizeEvents(view){
    $.ajax({
        type        : 'POST',
          url       : 'calendar.php',
          data      : {"machinesColors" : true},
          success   : function(res, status) {
                if(res){
                    machine_colors = JSON.parse(res);

                    for (var key in events_array) {
                        var machines = (events_array[key].value).split(" ");
                        var strColor = "repeating-linear-gradient(-45deg, ";
                        var pixels = 10;

                        for(var i=0; i<machines.length; i++){
                            if(machines.length === 1){
                                strColor += machine_colors[machines[i]]+", "+machine_colors[machines[i]]+" 10px";
                            }else if(machines.length > 1){
                                if(i === 0){
                                    strColor += machine_colors[machines[i]]+", "+machine_colors[machines[i]]+" "+pixels+"px, ";
                                }else if(i === machines.length-1){
                                    strColor += machine_colors[machines[i]]+" "+pixels+"px, ";
                                    pixels = pixels+10;
                                    strColor += machine_colors[machines[i]]+" "+pixels+"px";
                                }else{
                                    strColor += machine_colors[machines[i]]+" "+pixels+"px, ";
                                    pixels = pixels+10;
                                    strColor += machine_colors[machines[i]]+" "+pixels+"px, ";
                                }   
                            }
                        }
                        strColor+=")";

                        var eventClass = events_array[key].key;
                        $('.'+eventClass).css({"background-image":strColor});
                        $('.'+eventClass).css({"border":"none"});
                        $('.'+eventClass).css({"font-size":"13px"});
                    } 
                }
          },
          error           : function(xhr, desc, err) {
            console.log(xhr);
            console.log("Details: " + desc + "\nError:" + err);
          }
    }) 
}



function showEvents(start, end, timezone, callback, uname){
    $.ajax({
        type: 'POST',
        url:  'calendar.php',
        data: {"calendarEvents" : "true"},
        success: function(return_data, doc) {

            var allReservations = JSON.parse(return_data);

            reservationsToCal = [];
            allReservations.forEach(function(item){

                reservation = JSON.parse(item);

                var eventID = "event"+reservation.id;

                events_array.push({key:eventID, value:reservation.reserved_machines});

                var allDayEvent = false;

                var excl = "(non-exclusive)";

                if(reservation.exclusive === "1"){
                    excl = "(exclusive)";
                }

                /* Get start date and add one day */
                var splitStartDate = (((reservation.startDateTime).split(" "))[0]).split("-");
                var startDate_year = splitStartDate[0];
                var startDate_month = splitStartDate[1];
                var startDate_day = splitStartDate[2];

                /* Transform end date for JS */
                var splitEndDate = (((reservation.endDateTime).split(" "))[0]).split("-");
                var endDate_year = splitEndDate[0];
                var endDate_month = splitEndDate[1];
                var endDate_day = splitEndDate[2];

                var startDateTime = (reservation.startDateTime).split(" "); 
                var endDateTime = (reservation.endDateTime).split(" "); 
                
                /* Checking if event is all day */
                if((startDateTime[1] === endDateTime[1]) && ((startDate_year === endDate_year) && (startDate_month === endDate_month) && ((parseInt(startDate_day)+1) === parseInt(endDate_day)))){
                    allDayEvent = true;
                }

                var bool_edit = false;

                if(reservation.username === uname){
                    bool_edit = true;    
                }

                reservationsToCal.push({
                    id: reservation.id,
                    allDay: allDayEvent,
                    title: "("+reservation.username+")" + " - " + reservation.title + " - " + excl,
                    start: startDateTime[0]+"T"+startDateTime[1],
                    end: endDateTime[0]+"T"+endDateTime[1],
                    className: "event"+reservation.id,
                    username:reservation.username,
                    onlyTitle: reservation.title,
                    machines: reservation.reserved_machines,
                    currUserEvent: reservation.currUserEvent,
                    d1: startDateTime[0],
                    d2: endDateTime[0],
                    t1: startDateTime[1],
                    t2: endDateTime[1],
                    exclusive: reservation.exclusive,
                    machines_numbers: reservation.machines_numbers,
                    editable: bool_edit
                });    

            })
            callback(reservationsToCal);
        }
    });
}



function getFreeMachines(start_date, end_date, start_time, end_time){
    if(start_date == undefined || end_date == undefined || start_time == undefined || end_time == undefined){
        return;
    }

    if(start_date == "" || end_date == "" || start_time == "" || end_time == ""){
        return;
    }

    $.ajax({
        type        : 'POST',
          url       : 'calendar.php',
          data      : {"freeMachines" : true, "startDate" : start_date, "startTime" : start_time, "endDate" : end_date, "endTime" : end_time},
          success   : function(res, status) {
                if(res){
                    var machines = JSON.parse(res);
                    for (const [key, value] of Object.entries(machines)) { 
                        if(value === '1'){
                            $("input:checkbox[ics-name='"+ key +"']").prop("disabled", true);
                        }else{
                            $("input:checkbox[ics-name='"+ key +"']").prop("disabled", false);
                        }
                    }
                }
          },
          error           : function(xhr, desc, err) {
            console.log(xhr);
            console.log("Details: " + desc + "\nError:" + err);
          }
    }) 
}
