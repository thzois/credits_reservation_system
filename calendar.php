<?php  
	session_start();

	if(!isset($_SESSION["username"])){
		header('Location: index.php');
	}

	if(isset($_POST['machinesColors'])){
		$conn = require_once('config.php');

		$sql = "SELECT name,color FROM machines";
        $result = mysqli_query($conn, $sql);
        
        $machinesColors = array();

		while($row = mysqli_fetch_assoc($result)){
            $machinesColors[$row['name']] = $row['color'];
		}

		echo json_encode($machinesColors);
        exit;
	}



    if(isset($_POST['freeMachines']) && $_POST['freeMachines'] == "true"){
        $conn = require_once('config.php');

        $start_date = $_POST['startDate'];
        $end_date = $_POST['endDate'];
        $start_time = $_POST['startTime'];    
        $end_time = $_POST['endTime'];


        /* Edit time to match sql standards */
        $start_time = $_POST['startTime'];    
        $end_time = $_POST['endTime'];
        $start_time_parts = explode(":", $start_time);
        $end_time_parts = explode(":", $end_time);
        $start_time = $start_time_parts[0].":".$start_time_parts[1];
        $end_time = $end_time_parts[0].":".$end_time_parts[1];
        

        /* Edit date to match sql standards */
        $start_date = $_POST['startDate'];
        $end_date = $_POST['endDate'];
        $tmp_startdate = explode("/", $start_date);
        $tmp_enddate = explode("/", $end_date);
        $start_date = $tmp_startdate[2]."-".$tmp_startdate[1]."-".$tmp_startdate[0];
        $end_date = $tmp_enddate[2]."-".$tmp_enddate[1]."-".$tmp_enddate[0];
        
        $datetime_start = $start_date." ".$start_time;
        $datetime_end = $end_date." ".$end_time;      

        $username = $_SESSION['username'];
        

        $sql_2 = "SELECT * FROM machines";
        $res = mysqli_query($conn, $sql_2);

        while($row = mysqli_fetch_assoc($res)){
        	$machines[(string)$row['name']] = "0";
        }

        $sql = "SELECT * FROM reservations LEFT JOIN machines_reservations ON reservations.id = machines_reservations.reservation_id LEFT JOIN machines ON machines_reservations.machine_id = machines.id WHERE ((datetime_start < '$datetime_start' AND datetime_end > '$datetime_start') OR (datetime_start < '$datetime_end' AND datetime_end > '$datetime_end') OR (datetime_start > '$datetime_start' AND datetime_end < '$datetime_end')) AND exclusive='1' AND username!='$username'";


        $result = mysqli_query($conn, $sql);

        while($row = mysqli_fetch_assoc($result)){
            $machines[(string)$row['name']] = "1";
        }

        echo json_encode($machines);
        exit;
    }



    if(isset($_POST['calendarEvents']) && $_POST['calendarEvents'] == "true"){
        $conn = require_once('config.php');

        $today = date("Y-m-d"); 
        $sql = "SELECT reservations.id, username, datetime_start, datetime_end, exclusive, event_title, GROUP_CONCAT(DISTINCT machines.id) AS machines_number, GROUP_CONCAT(DISTINCT machines.name) AS machines FROM reservations LEFT JOIN machines_reservations ON reservations.id = machines_reservations.reservation_id LEFT JOIN machines ON machines_reservations.machine_id = machines.id GROUP BY reservations.id";

        $result = mysqli_query($conn, $sql);
        $allReservations = array();
    
        while($reservation = mysqli_fetch_assoc($result)){
           
            $addReservation = new StdClass;
            $addReservation->id = $reservation['id'];
            $addReservation->startDateTime = $reservation['datetime_start'];
            $addReservation->endDateTime = $reservation['datetime_end'];
            $addReservation->username = $reservation['username'];
            $addReservation->title = $reservation['event_title'];
            $addReservation->exclusive = $reservation['exclusive'];
            $addReservation->reserved_machines = str_replace(",", " ", $reservation['machines']);
            $addReservation->machines_numbers = str_replace(",", " ", $reservation['machines_number']);
            
            if($reservation['username'] == $_SESSION['username']){
                $addReservation->currUserEvent = "true";
            }else{
                $addReservation->currUserEvent = "false";
            }
            
            $jsonReservations = json_encode($addReservation);
            $allReservations[] = $jsonReservations;
        }
        
        echo json_encode($allReservations);
        exit;
    }



    if(isset($_POST['reservation']) || isset($_POST['editReservation']) || isset($_POST['confirmUpdate']) || isset($_POST['confirmConsume'])){

        $conn = require_once('config.php');

        /* Form values */
        $title = $_POST['title'];
        
        $start_date = $_POST['startDate'];
        $end_date = $_POST['endDate'];
        
        /* Edit time to match sql standards */
        $start_time = $_POST['startTime'];    
        $end_time = $_POST['endTime'];
        $start_time_parts = explode(":", $start_time);
        $end_time_parts = explode(":", $end_time);
        $start_time = $start_time_parts[0].":".$start_time_parts[1];
        $end_time = $end_time_parts[0].":".$end_time_parts[1];
        
        $tmp_startdate = explode("/", $start_date);
        $tmp_enddate = explode("/", $end_date);
        $start_date = $tmp_startdate[2]."-".$tmp_startdate[1]."-".$tmp_startdate[0];
        $end_date = $tmp_enddate[2]."-".$tmp_enddate[1]."-".$tmp_enddate[0];
        
        $datetime_start = $start_date." ".$start_time;
        $datetime_end = $end_date." ".$end_time;

        $exclusive = '0';
        if(isset($_POST['exclusive'])){
            $exclusive = $_POST['exclusive'];
        }

        $sql = "SELECT * FROM machines WHERE id IN (". implode(", ", $_POST['machine_name']) .")";
        $result = mysqli_query($conn, $sql);

       
		$machines_checkbox = $_POST['machine_name'];
		$machines_names = "";

        while($row = mysqli_fetch_assoc($result)){
        	$machines_names .= $row['name'].", ";
        }

        $machines_names = rtrim($machines_names, ', ');

        $machines_query_conflict = "";
        
        /* Calculate credits to consume taking into consideration time and date! */
        $date1 = new DateTime($start_date."T".$start_time);
        $date2 = new DateTime($end_date."T".$end_time);
        $diff = $date2->diff($date1);
        $hours = $diff->h;
        $minutes = $diff->i;
        $hours = $hours + ($diff->days*24);

        $consumeCredits = ((($minutes/60)+$hours)*1);

        /* Get current user credits */
        $username = $_SESSION['username'];
        $sql_2 = "SELECT credits FROM users WHERE BINARY username ='$username'";
        $result = mysqli_query($conn, $sql_2);
        $user_credits = mysqli_fetch_assoc($result);


        /* Obtain lock on table reservations */
        /* Check requested machines availability for booking - conflict date, time, machines */
        $sql_lock = "LOCK TABLE reservations, users WRITE, reservations, users READ;";
        mysqli_query($conn, $sql_lock);

        $get_machines = "SELECT * FROM reservations LEFT JOIN machines_reservations ON machines_reservations.reservation_id = reservations.id WHERE ((datetime_start < '$datetime_start' AND datetime_end > '$datetime_start') OR (datetime_start < '$datetime_end' AND datetime_end > '$datetime_end') OR (datetime_start > '$datetime_start' AND datetime_end < '$datetime_end')) AND machines_reservations.machine_id IN (" .implode (', ', $_POST['machine_name']) . ") AND username != '$username' ";

        if($exclusive == '0'){
            $get_machines.= "AND exclusive='1'";
        }
        
        //Possible bug -> Maybe remove comments
        //if (isset($_POST['editReservation']) || isset($_POST['confirmUpdate']) || isset($_POST['confirmConsume'])){
        //    $get_machines .= " AND username!='$username'";
        //}

        $result_conflict = mysqli_query($conn, $get_machines);

        /* Check for conflict in dates, times and machine */
        if(mysqli_num_rows($result_conflict) === 0){
            /* New reservation */
            if(isset($_POST['reservation'])){
                
                /* Check if the user has already made the same reservation on the same machines (or some included) */
                /* Create the reservation */

				$sql = "INSERT INTO reservations (event_title, username, datetime_start, datetime_end, all_day, exclusive, consumed_credits) VALUES ('$title', '$username', '$datetime_start', '$datetime_end', '0', '$exclusive', ";

				$reservation_id = 0;

                /* New Exclusive reservation */
                if($exclusive == '1'){
                    if($user_credits['credits'] >= $consumeCredits){

                        /* Update user credits */
                        $newCredits = json_decode($user_credits['credits']) - $consumeCredits;

                        $sql_3 = "UPDATE users SET credits='$newCredits' WHERE username='$username'";
                        mysqli_query($conn, $sql_3);
                        
                        exec("php send_email.php new \"" . $username . "\" \"" . $title . "\" \"" . $datetime_start . "\" \"" . $datetime_end . "\" \"" . $machines_names . "\" > /dev/null 2> /dev/null &");  
                        
                    }else{
                        echo "failed";
                        $reservation_id = -1;
                    }
                /* New NON exclusive reservation */
                }else{
                      $consumeCredits = '0';
                }
                
                if($reservation_id != -1){

                    $sql.=" '$consumeCredits')";
                    mysqli_query($conn, $sql);
                    
                	$reservation_id = mysqli_insert_id($conn);

                	foreach ($_POST["machine_name"] as $machine_id) {
	                	$sql = "INSERT INTO machines_reservations (reservation_id, machine_id) VALUES ('$reservation_id', '$machine_id')"; 
	                	mysqli_query($conn, $sql);
	                }

	               	echo "success";
                }

            /* Edit reservation */
            }else if(isset($_POST['editReservation'])){
                $reservationID = $_POST['resID'];

                /* SQL Query for update */
                $sql_update = "UPDATE reservations SET event_title='$title', datetime_start='$datetime_start', datetime_end='$datetime_end', all_day='0', exclusive='$exclusive'";

                if($exclusive == '1'){
                    /* Get consumed credits of current reservation */
                    $get_credits = "SELECT consumed_credits, datetime_start, datetime_end, exclusive FROM reservations WHERE id='$reservationID'";
                    $cr = mysqli_query($conn, $get_credits);
                    $res_credits = mysqli_fetch_assoc($cr);
                    
                    $today = date("Y-m-d H:m:i");
                    $timediff = strtotime($res_credits['datetime_start']) - strtotime($today);
                    
                    /* Only if there is a transfer in start date -> penalty on credits */
                    $date_new = strtotime($start_date);
                    $date_old = strtotime(explode(" ", $res_credits['datetime_start'])[0]);
                    $transfer_date = 0;
                
                    if($date_new - $date_old > 0){
                        $transfer_date = 1;    
                    } 
                    
                    /* Everything ok */
                    if(($transfer_date == 0) || ($timediff > 86400)){
                        if($consumeCredits - json_decode($res_credits['consumed_credits']) > 0){
                            $add_consume_credits = $consumeCredits - json_decode($res_credits['consumed_credits']);

                            /* Check if user has enough credits to update the reservation */
                            if($user_credits['credits'] >= $add_consume_credits){
                                /* Consume the extra needed credits from the user */
                                $sql_user_credits = "UPDATE users SET credits = credits - '$add_consume_credits' WHERE username='$username'";
                                mysqli_query($conn, $sql_user_credits);

                                /* Update the reservation */
                                $sql_update.= ", consumed_credits='$consumeCredits' WHERE id='$reservationID' AND username='$username'";
                                mysqli_query($conn, $sql_update);

                                echo "updated";
                            }else{
                                echo "failed";
                            }
                        }else if($consumeCredits - json_decode($res_credits['consumed_credits']) < 0){
                            /* Return some credits */
                            $return_credits = json_decode($res_credits['consumed_credits']) - $consumeCredits;
                            $sql_ret_cr = "UPDATE users SET credits = credits+'$return_credits' WHERE username='$username'";
                            mysqli_query($conn, $sql_ret_cr);

                            /* Update the reservation now and add new consumed credits! */
                            $sql_update .= ", consumed_credits='$consumeCredits' WHERE id='$reservationID' AND username='$username'";
                            mysqli_query($conn, $sql_update);

                            echo "updated";
                        }else{
                            /* Just update the reservation - credits are the same */
                            $sql_update .=" WHERE id='$reservationID' AND username='$username'";

                            mysqli_query($conn, $sql_update);
                            echo "updated";
                        }


                        if($res_credits['exclusive'] == '0'){
                           exec("php send_email.php edit_change_to_exclusive \"" . $username . "\" \"" . $title . "\" \"" . $datetime_start . "\" \"" . $datetime_end . "\" \"" . $machines_names  . "\" > /dev/null 2> /dev/null &");  
                                
                        }else{
                            exec("php send_email.php edit \"" . $username . "\" \"" . $title . "\" \"" . $datetime_start . "\" \"" . $datetime_end . "\" \"" . $machines_names  . "\" > /dev/null 2> /dev/null &");      
                        }

                    /* Date transfer made less than 24 hours of the event, also case of transfering and old event to a new date! */
                    }else if(($transfer_date == 1) && ($timediff <= 86400)){

                        /* Consume credits again and make the reservation  */
                        echo "edit_consume_again";
                    }

                /* Non exclusive reservation */
                }else{
                    $sql_get_res_credits = "SELECT consumed_credits FROM reservations WHERE id='$reservationID' AND username='$username'";
                    $res = mysqli_query($conn, $sql_get_res_credits);
                    $credits = mysqli_fetch_assoc($res);
                    $consumeCredits = '0';
                    
                    if(json_decode($credits['consumed_credits']) > 0){
                        
                        $sql_ref = "SELECT datetime_start, consumed_credits, exclusive FROM reservations WHERE BINARY id='$reservationID' AND username='$username'";
                        $result = mysqli_query($conn, $sql_ref);
                        $start = mysqli_fetch_assoc($result);

                        /* if consumed credits before return them if one day before and update making non exclusive */
                        $today = date("Y-m-d H:m:i");
                        $timediff = strtotime($start['datetime_start']) - strtotime($today);

                        $credits = $start['consumed_credits'];

                        /* Get a refund */
                        if($timediff > 86400){
                            $sql_send_ref = "UPDATE users SET credits=credits+'$credits'";
                            mysqli_query($conn, $sql_send_ref);
                            
                            $sql_update.= ", consumed_credits='$consumeCredits' WHERE id='$reservationID' AND username='$username'";
                            mysqli_query($conn, $sql_update);
                            
                            $machines = str_replace(',', '', $machines_query_conflict);
                            
                            exec("php send_email.php edit_change_from_exclusive  \"" . $username . "\" \"" . $title . "\" \"" . $datetime_start . "\" \"" . $datetime_end . "\" \"" . $machines . "\" > /dev/null 2> /dev/null &");  
                                
                            echo "updated";
                        }else{
                            echo "edit_but_no_refund";
                        }
                    }else{
                        $sql_update.= ", consumed_credits='$consumeCredits' WHERE id='$reservationID' AND username='$username'";
                        mysqli_query($conn, $sql_update);
                        echo "updated";
                    }
                }

		        $sql = "DELETE FROM machines_reservations WHERE reservation_id = '$reservationID'"; 
			    	mysqli_query($conn, $sql);

		        foreach ($_POST["machine_name"] as $machine_id) {
		        	$sql = "INSERT INTO machines_reservations (reservation_id, machine_id) VALUES ('$reservationID', '$machine_id')"; 
		        	mysqli_query($conn, $sql);
		        }
            }else if(isset($_POST['confirmUpdate']) || isset($_POST['confirmConsume'])){
            	$reservationID = $_POST['resID'];

            	/* SQL Query for update */
                $sql_update = "UPDATE reservations SET event_title='$title', datetime_start='$datetime_start', datetime_end='$datetime_end', all_day='0', exclusive='$exclusive'";

                $consumeCredits = 0;
                $message = "edit_change_from_exclusive";

                if(isset($_POST['confirmConsume'])){

                	$sql_credits = "SELECT credits FROM users WHERE BINARY username='$username'";
		            $r = mysqli_query($conn, $sql_credits);
		            $user_credits = mysqli_fetch_assoc($r);

                	$date1 = new DateTime($start_date."T".$start_time);
		            $date2 = new DateTime($end_date."T".$end_time);
		            $diff = $date2->diff($date1);
		            $hours = $diff->h;
		            $minutes = $diff->i;
		            $hours = $hours + ($diff->days*24);
		            $consumeCredits = ((($minutes/60)+$hours)*1);

		            if($user_credits['credits'] >= $consumeCredits){
                		$sql_user_credits = "UPDATE users SET credits = credits - '$consumeCredits' WHERE username='$username'";
                		mysqli_query($conn, $sql_user_credits);
                		
                		$message = "edit";
                	}else{
                		echo "failed";
                		exit;
            		}
                }

                $sql_update.= ", consumed_credits='$consumeCredits' WHERE id='$reservationID' AND username='$username'";
                mysqli_query($conn, $sql_update);
                echo "updated";

                exec("php send_email.php" . $message . "\"" . $username . "\" \"" . $title . "\" \"" . $datetime_start . "\" \"" . $datetime_end . "\" \"" . $machines_names . "\" > /dev/null 2> /dev/null &");  
                exit;
            }
        }else{
            echo "conflict";
        }

        $sql_unlock = "UNLOCK TABLES;";
        mysqli_query($conn, $sql_unlock);
        
        exit;
    }



    if(isset($_POST['cancelReservation'])){
        $conn = require_once('config.php');
        $reservationID = $_POST['cancelReservation'];
        
        /* Check for refund credits */
        $sql_ref = "SELECT reservations.id, username, datetime_start, datetime_end, exclusive, event_title, consumed_credits, GROUP_CONCAT(DISTINCT machines.id) AS machines_number, GROUP_CONCAT(DISTINCT machines.name) AS machines FROM reservations LEFT JOIN machines_reservations ON reservations.id = machines_reservations.reservation_id LEFT JOIN machines ON machines_reservations.machine_id = machines.id WHERE BINARY reservations.id='$reservationID' GROUP BY reservations.id";

        $result = mysqli_query($conn, $sql_ref);
        $start = mysqli_fetch_assoc($result);
        
        if($start['exclusive'] == '1'){


            $today = date("Y-m-d H:m:i");
            $timediff = strtotime($start['datetime_start']) - strtotime($today);

            $credits = $start['consumed_credits'];

            /* Get a refund */
            if($timediff > 86400){
                $sql_send_ref = "UPDATE users SET credits=credits+'$credits'";
                mysqli_query($conn, $sql_send_ref);
            }
            
            $machines = str_replace(","," ", $start['machines']);

            exec("php send_email.php cancel \"" . $start['username'] . "\" \"" . $start['event_title'] . "\" \"" . substr($start['datetime_start'], 0, -3) . "\" \"" . substr($start['datetime_end'], 0, -3) . "\" \"" . $machines . "\" > /dev/null 2> /dev/null &");
        }
        
        /* Cancel reservation */
        $sql = "DELETE FROM machines_reservations WHERE reservation_id='$reservationID'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM reservations WHERE id='$reservationID'";
        mysqli_query($conn, $sql);

        echo 'reservationCancelled';
        exit;
    }

    require_once('head.php');
?>
<body class="not-home" style="margin-top:55px">
	<div class="colored-navbar-brand">
		<header id="header" class="overflow-x-hidden-xss">
            <?php require_once('nav_res.php'); ?>
		</header>
		<div class="clear"></div>	

		<!-- start Main Wrapper -->
		<div class="main-wrapper">
			<div class="two-tone-layout size-2">
                <div class="equal-content-sidebar">
                    <div class="alert alert-info" role="alert" style="margin-top:52px;">
                        <div class="content" style="margin-bottom:10px;">
                           <h5 class="text-primary"><i class="fa fa-lightbulb-o font16"></i>&nbsp;&nbsp;<strong>System policies</strong></h5>
                            <ul>
                                <li style="list-style-type: circle; margin-left:20px;"><strong>Credits</strong> are consumed only for <strong>exclusive</strong> reservations</li>
                                <li style="list-style-type: circle; margin-left:20px;"><strong>You won't get a refund if</strong> you <strong>transfer the date</strong> of your reservation, <strong>24 hours before</strong> the actual day</li>
                                <li style="list-style-type: circle; margin-left:20px;"><strong>You won't get a refund if</strong> you <strong>cancel</strong> your reservation, <strong>24 hours before</strong> the actual day</li>
                                <li style="list-style-type: circle; margin-left:20px;"><strong>You won't get a refund if</strong> you <strong>change</strong> an <strong>exclusive</strong> reservation to <strong>non-exclusive</strong>, <strong>24 hours before</strong> the actual day</li>
                            </ul>
                        </div>
                    </div>
				</div>
			</div>
            <div class="container">
                <div class="section-title" style="margin-bottom:0">
                    <h2 class="text-left">Reservations</h2>
                </div>
                <div class="bb"></div>
                <div class="bb"></div>

                    <div class="top-table">
                    	<table class="momentum" style="border:none!important;">
							<?php                    		
								$sql = "SELECT * FROM machines";
                                $result = mysqli_query($conn, $sql);
                                $i = 0;

                                while($row = mysqli_fetch_assoc($result)){

                                	if($i%7 == 0){
                                		echo "<tr height='35'>";
                                	}

                                		echo"<td style='min-width:160px'><span class='dot ml-20' style='background-color:".$row['color']."'></span>&nbsp;<span class='momentumText'>".$row['name']." <strong>".$row['features']."</strong></span></td>";
                                		$i++;


                                	if($i%7 == 0){
                                		echo "</tr>";
                                	}
                                }
                            ?>
                        </table>
                    </div>
                    <script>
                        var fixmeTop = $('.top-table').offset().top;
                        $(window).scroll(function() {
                            var currentScroll = $(window).scrollTop();
                            if (currentScroll >= fixmeTop-108) {
                                $('.top-table').css({
                                    position: 'fixed',
                                    top: '108px',
                                    display: '',
                                    background: '#F7F7F7',
                                    zIndex:'1000',
                                });

                                $('.top-table table').css("marginBottom", "0");
                            } else {
                                $('.top-table').css({
                                    position: 'relative',
                                    display: 'inline-block',
                                    top: ''
                                });
                                $('.top-table table').css("marginBottom", "");
                            }
                        });
                    </script>
                    <div class="top-reservation-button mb-60" style="display:none">
                        <div id="calendarModal">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" onclick="clearResForm()" id="modalForm" data-backdrop="static" data-keyboard="false">New Reservation</button>
                        </div> 
                    </div>
                    <div style="width:100%!important; overflow:hidden;">
                        <div id='calendar'></div>
                    </div>
                    
                     <!-- Modal -->
                    <div id="eventInfo"> </div>
          
                    <!---------------------------------- Start modal ------------------------------------->
                    <div class="modal fade" id="myModal" role="dialog">
                        <div class="modal-dialog modal-lg">
                          <!-- Modal content-->
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title">New Reservation</h4>
                            </div>
                            <form method="post" onsubmit="return false;" id="reserveForm" style="margin-left:20px;">
                                <div style="width:70%">
                                    <!-- Title -->
                                    <div class="form-group">
                                        <div class="form-group">
                                          <h5><span class="text-danger">*</span>Title:</h5>
                                          <input type="text" class="form-control" id="title" placeholder="Title" name="title" required>
                                        </div>
                                    </div>
                                </div>
                                <!-- From - To -->
                                <div class="form-group">
                                    <div class="form-group">
                                      <h5><span class="text-danger label-left">*</span>From:</h5>
                                      <div style="width:50%">
                                          <div class="input-group">
                                              <input type="text" id="startDate" class="form-control" name="startDate" placeholder="dd/mm/yyyy" required>
                                              <span class="input-group-addon time">-</span>
                                              <select onchange="getFreeMachines($('#startDate').val(), $('#endDate').val(), $('#startTime').val() , $('#endTime').val());" id="startTime" name="startTime" class="form-control time" required> 
                                                  <option disabled value="" selected> </option>
                                                  <option value="00:00:00">00:00</option>
                                                  <option value="00:30:00">00:30</option>
                                                  <option value="01:00:00">01:00</option>
                                                  <option value="01:30:00">01:30</option>
                                                  <option value="02:00:00">02:00</option>
                                                  <option value="02:30:00">02:30</option>
                                                  <option value="03:00:00">03:00</option>
                                                  <option value="03:30:00">03:30</option>
                                                  <option value="04:00:00">04:00</option>
                                                  <option value="04:30:00">04:30</option>
                                                  <option value="05:00:00">05:00</option>
                                                  <option value="05:30:00">05:30</option>
                                                  <option value="06:00:00">06:00</option>
                                                  <option value="06:30:00">06:30</option>
                                                  <option value="07:00:00">07:00</option>
                                                  <option value="07:30:00">07:30</option>
                                                  <option value="08:00:00">08:00</option>
                                                  <option value="08:30:00">08:30</option>
                                                  <option value="09:00:00">09:00</option>
                                                  <option value="09:30:00">09:30</option>
                                                  <option value="10:00:00">10:00</option>
                                                  <option value="10:30:00">10:30</option>
                                                  <option value="11:00:00">11:00</option>
                                                  <option value="11:30:00">11:30</option>
                                                  
                                                  <option value="12:00:00">12:00</option>
                                                  <option value="12:30:00">12:30</option>
                                                  <option value="13:00:00">13:00</option>
                                                  <option value="13:30:00">13:30</option>
                                                  <option value="14:00:00">14:00</option>
                                                  <option value="14:30:00">14:30</option>
                                                  <option value="15:00:00">15:00</option>
                                                  <option value="15:30:00">15:30</option>
                                                  <option value="16:00:00">16:00</option>
                                                  <option value="16:30:00">16:30</option>
                                                  <option value="17:00:00">17:00</option>
                                                  <option value="17:30:00">17:30</option>
                                                  <option value="18:00:00">18:00</option>
                                                  <option value="18:30:00">18:30</option>
                                                  <option value="19:00:00">19:00</option>
                                                  <option value="19:30:00">19:30</option>
                                                  <option value="20:00:00">20:00</option>
                                                  <option value="20:30:00">20:30</option>
                                                  <option value="21:00:00">21:00</option>
                                                  <option value="21:30:00">21:30</option>
                                                  <option value="22:00:00">22:00</option>
                                                  <option value="22:30:00">22:30</option>
                                                  <option value="23:00:00">23:00</option>
                                                  <option value="23:30:00">23:30</option>
                                              </select>
                                          </div>
                                      </div>
                                      <div style="width:68%">
                                        <div class="checkbox-block font-icon-checkbox" style="float:right; margin-top:-32px">
                                            <input id="amenities-checkbox-0" class="allDay" exclusive-check="exclusive-check" type="checkbox" name="exclusive" value="1"/>
                                            <label for="amenities-checkbox-0"><strong style="font-size:18px;">Exclusive</strong></label>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                
                                <div style="width:50%">
                                    <div class="form-group">
                                        <div class="form-group">
                                          <h5><span class="text-danger">*</span>To:</h5>
                                          <div class="input-group">
                                                <input type="text" id="endDate" class="form-control" placeholder="dd/mm/yyyy" name="endDate" required>
                                                <span class="input-group-addon time">-</span>
                                              <select onchange="getFreeMachines($('#startDate').val(), $('#endDate').val(), $('#startTime').val() , $('#endTime').val());" id="endTime" name="endTime" class="form-control time" required> 
                                                  <option disabled value="" selected> </option>
                                                  <option value="00:00:00">00:00</option>
                                                  <option value="00:30:00">00:30</option>
                                                  <option value="01:00:00">01:00</option>
                                                  <option value="01:30:00">01:30</option>
                                                  <option value="02:00:00">02:00</option>
                                                  <option value="02:30:00">02:30</option>
                                                  <option value="03:00:00">03:00</option>
                                                  <option value="03:30:00">03:30</option>
                                                  <option value="04:00:00">04:00</option>
                                                  <option value="04:30:00">04:30</option>
                                                  <option value="05:00:00">05:00</option>
                                                  <option value="05:30:00">05:30</option>
                                                  <option value="06:00:00">06:00</option>
                                                  <option value="06:30:00">06:30</option>
                                                  <option value="07:00:00">07:00</option>
                                                  <option value="07:30:00">07:30</option>
                                                  <option value="08:00:00">08:00</option>
                                                  <option value="08:30:00">08:30</option>
                                                  <option value="09:00:00">09:00</option>
                                                  <option value="09:30:00">09:30</option>
                                                  <option value="10:00:00">10:00</option>
                                                  <option value="10:30:00">10:30</option>
                                                  <option value="11:00:00">11:00</option>
                                                  <option value="11:30:00">11:30</option>
                                                  
                                                  <option value="12:00:00">12:00</option>
                                                  <option value="12:30:00">12:30</option>
                                                  <option value="13:00:00">13:00</option>
                                                  <option value="13:30:00">13:30</option>
                                                  <option value="14:00:00">14:00</option>
                                                  <option value="14:30:00">14:30</option>
                                                  <option value="15:00:00">15:00</option>
                                                  <option value="15:30:00">15:30</option>
                                                  <option value="16:00:00">16:00</option>
                                                  <option value="16:30:00">16:30</option>
                                                  <option value="17:00:00">17:00</option>
                                                  <option value="17:30:00">17:30</option>
                                                  <option value="18:00:00">18:00</option>
                                                  <option value="18:30:00">18:30</option>
                                                  <option value="19:00:00">19:00</option>
                                                  <option value="19:30:00">19:30</option>
                                                  <option value="20:00:00">20:00</option>
                                                  <option value="20:30:00">20:30</option>
                                                  <option value="21:00:00">21:00</option>
                                                  <option value="21:30:00">21:30</option>
                                                  <option value="22:00:00">22:00</option>
                                                  <option value="22:30:00">22:30</option>
                                                  <option value="23:00:00">23:00</option>
                                                  <option value="23:30:00">23:30</option>
                                              </select>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <script>
                                    $(document).ready(function() {
                                        $('.allDay').click(function() {
                                            /* Hide or show time */
                                        });
                                    });
                                    
                                   $(function() {
                                        var date = new Date();
                                        date.setDate(date.getDate()-1);
                                       
                                        $("#startDate").datepicker({
                                            format: 'dd/mm/yyyy',
                                            weekStart: 1,
                                            startDate: date
                                        }).on('changeDate', function(selected){
                                            var minDate = $('#startDate').val();
                                            $('#endDate').datepicker('setStartDate', minDate);
                                            $('.datepicker').hide();
                                            getFreeMachines($('#startDate').val(), $('#endDate').val(), $('#startTime').val() , $('#endTime').val());
                                        });

                                        $("#endDate").datepicker({
                                            format: 'dd/mm/yyyy',
                                            weekStart: 1,
                                        }).on('changeDate', function(){
                                            var maxDate = $('#endDate').val();
                                            $('#startDate').datepicker('setEndDate', maxDate);
                                            $('.datepicker').hide();
                                            getFreeMachines($('#startDate').val(), $('#endDate').val(), $('#startTime').val() , $('#endTime').val());
                                        });
                                       
                                   });
                                </script>

                                <!-- Which Machines -->
                                <div class="form-group row gap-15 mt-20 machines-group">
                                    <div class="col-sm-8 col-md-8 pt-5">
                                        <div class="row gap-15 pl-5">
                                            <div class="checkbox-block font-icon-checkbox questionGPU" style="margin-top:7px;">
                                                <label for="amenities-checkbox-0"><strong style="font-size:18px;">Do you want to reserve GPUs?</strong></label>
                                                <input type="checkbox" id="gpusNeed" name="gpusNeed">
                                            </div>
                                            <h5><span class="text-danger">*</span>Reserve: </h5>
                                            <script>                                                      
                                                $('#gpusNeed').change(function () {
                                                    if ($('#gpusNeed').is(":checked")){
                                                        $(".gpuMachine").show();

                                                    }else{
                                                        $(".gpuMachine").hide();                                                        
                                                    }
                                                }); 
                                            </script>
                                            <?php
                                                $sql = "SELECT * FROM machines";
                                                $result = mysqli_query($conn, $sql);

                                                while($row = mysqli_fetch_assoc($result)){
                                                    
                                                    if($row['features'] != ''){
                                                        echo "<div hidden class='col-sm-4 machines gpuMachine'>";
                                                    }else{
                                                        echo "<div class='col-sm-4 machines'>";
                                                    }
                                                
                                                    echo "<div class='checkbox-block font-icon-checkbox'> 
                                                                <input id='amenities-checkbox-".$row['id']."' ics-name='".$row['name']."' name='machine_name[]' value='".$row['id']."' type='checkbox'/>
                                                                <label for='amenities-checkbox-".$row['id']."'>".$row['name']."<strong class='text-danger'> ".$row['features']."</strong></label>
                                                            </div>
                                                         </div>";
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                  <button onclick="checkForm(this.id)" name="reservation" class="save-button btn btn-primary">Save</button>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>
                    <!---------------------------------- End modal ------------------------------------->
                    
                    <script>
                        $('#calendar').fullCalendar({
                            height:1219,
                            schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                            scrollTime: '08:00',
                            timeFormat: 'HH:mm',
                            slotLabelFormat: "HH:mm",
                            columnFormat: "D/M",
                            titleFormat: "D MMMM YYYY",
                            header: {
                                left: 'today prev,next',
                                center: 'title',
                                right: 'agendaDay, agendaWeek, listWeek'
                            },
                            defaultView: 'agendaWeek',
                            buttonText:{
                                listWeek:'List'
                            },
                            events: function(start, end, timezone, callback) {
                                        var uname =  <?php echo json_encode($_SESSION['username']); ?>;
                                        showEvents(start, end, timezone, callback, uname);
                                    },

                           eventClick: function(event) {
                                            eventClick(event);
                                       },

                            /* Colorize & Style events */
                            eventAfterAllRender: function(){
                                                    colorizeEvents();
                                                 },

                            eventDrop: function(event, delta, revertFunc){
                                          eventResizeDrop(event,delta,revertFunc);
                                       }, 

                            select: function(start, end, jsEvent, view){   
                                        dateTimeClick(start, end);
                                        $('#calendar').fullCalendar('unselect');
                                    },
                            selectable: true,

                            eventResize: function(event, delta, revertFunc){
                                           eventResizeDrop(event,delta,revertFunc);
                                         }
                           /* viewRender: function(currentView){
                                           disableFutureWeeks(currentView);
                                        } */
                        });
                    </script>
                    
                </div>
                <div class="mb-80"></div>
			</div>
		</div>
	</div>
</body>
<?php require_once('footer.php'); ?>
