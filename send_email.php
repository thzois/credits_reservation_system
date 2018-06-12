<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require './PHPMailer/PHPMailer.php';
    require './PHPMailer/SMTP.php';
    require './PHPMailer/Exception.php';

    function send_email($type, $username, $title, $start_datetime, $end_datetime, $machines){
        
        $mail = new PHPMailer(true);

        $emailBody = '<html><body>';

        if($type=="new"){
            $emailBody .= "<p>User `".$username."` created a <strong>new</strong> reservation `".$title."`</p>";  
            $mail->Subject = 'New exclusive reservation';
        }else if($type=="edit"){
            $emailBody .= "<p>User `".$username."` made <strong>changes</strong> to reservation `".$title."`</p>";   
            $mail->Subject = 'Changes to exclusive reservation';
        }else if($type=="edit_change_from_exclusive"){
            $emailBody .= "<p>User `".$username."` changed reservation `".$title."` from exclusive to <strong>non-exclusive</strong></p>";
            $mail->Subject = 'Changes to exclusive reservation';
        }else if($type=="cancel"){
            $emailBody .= "<p>User `".$username."` <strong>canceled</strong> reservation `".$title."`</p>";
            $mail->Subject = 'Canceled exclusive reservation';
        }else if($type == "edit_change_to_exclusive"){
            $emailBody .= "<p>User `".$username."` changed reservation `".$title."` from non-exclusive to <strong>exclusive</strong></p>";
            $mail->Subject = 'Changes to non-exclusive reservation';
        }

        $format = 'Y-m-d H:i';
        $start_datetime = DateTime::createFromFormat($format, $start_datetime);
        $end_datetime = DateTime::createFromFormat($format, $end_datetime);
        
        $emailBody .= "<p>From: ".$start_datetime->format('d-m-Y H:i')."</p>";
        $emailBody .= "<p>To: ".$end_datetime->format('d-m-Y H:i')."</p>";
        $emailBody .= "<p>Machines: ".$machines."</p>";

        $emailBody .= "<br>Reservation System";
        $emailBody .= "<br><br>---------------------------------------------------------------------";
        $emailBody .= "<br>This is an automatically generated email - please do not reply";
        $emailBody .= "</body></html>";

        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = '';
        $mail->Port = ;
        $mail->setFrom('', '');
        $mail->addAddress('', '');
        $mail->msgHTML($emailBody);
        $mail->send();
    }

    send_email($argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6]);
?>
