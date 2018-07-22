<?php
session_start();

if(isset($_SESSION["username"])){
	header('Location: calendar.php');
}

require_once('head.php');
?>
<body>
	<div class="main-wrapper">
		<div class="hero" style="background-image:url('images/login_bg_3.png'); height:93.5vh;">
			<div class="container">	
				<div  class="hero-texting">
				<img style="margin:0 auto;" src="./images/carv-logo.png">
					<h1 style="margin-top:13px;">Resevation System</h1>
										<!--<p class="index-heading" style="margin-bottom:40px; color:#f4c667;">Reserve only for exclusive use!</p>-->
					<p>SIGN IN</p>
				</div>
				<div class="main-search-wrapper">
                    <div class="mt-50"></div>
					<div class="inner animated">
                        <div style="text-align:center!important; margin-bottom:20px;">
                            <a href="mailto:&subject=Reservation System - Request" target="_top" style="font-size:18px; font-weight:600;color:#f4c667;">Send us an e-mail</a>    
                        </div>
						<form action="index.php" method="post" class="row">
                            <?php

                            if(isset($_POST['login'])){
                                login();
                            }

                            function login(){
                                $conn = require_once 'config.php';
                                $ldap_config = require_once 'ldap_config.php';
                                $ldap_server = ldap_connect($ldap_config['hostname'], $ldap_config['port']);

                                $user = $_POST["username"];
                                $password = $_POST["password"];
                                
                                $search_attribute = $ldap_config['search_attribute'];
                                $filter = $ldap_config['filter'];
                                $base_dn = $ldap_config['base_dn'];
                                $bind_dn = $ldap_config['bind_dn'];
                                $bind_pass = $ldap_config['bind_pass'];

                                /**
                                 * @param string @user
                                 * A ldap username or email or sAMAccountName
                                 * @param string @password
                                 * An optional password linked to the user, if not provided an anonymous bind is attempted
                                 * @param string @search_attribute
                                 * The attribute used on your LDAP to identify user (uid, email, cn, sAMAccountName)      
                                 * @param string @filter
                                 * An optional filter to search in LDAP (ex : objectClass = person).
                                 * @param string @base_dn
                                 * The LDAP base DN. 
                                 * @param string @bind_dn
                                 * The directory name of a service user to bind before search. Must be a user with read permission on LDAP. 
                                 * @param string @bind_pass
                                 * The password associated to the service user to bind before search. 
                                 * 
                                 * @return 
                                 * TRUE if the user is identified and can access to the LDAP server
                                 * and FALSE if it isn't  
                                 */
                                
                                if ($bind_dn != '' && $bind_dn != null){
                                    $bind_result=ldap_bind($ldap_server,$bind_dn,$bind_pass);
                                    if (!$bind_result){
                                        throw new Exception('An error has occured during ldap_bind execution. Please check parameter of LDAP/checkLogin, and make sure that user provided have read permission on LDAP.');
                                    }
                                }

                                if ($filter!="" && $filter != null) {
                                    $search_filter = '(&(' . $search_attribute . '=' . $user . ')' . $filter .')';
                                }
                                else{
                                    $search_filter = $search_attribute . '=' . $user;
                                }

                                $result = ldap_search($ldap_server, $base_dn, $search_filter);

                                if (!$result){
                                    throw new Exception('An error has occured during ldap_search execution. Please check parameter of LDAP/checkLogin.');
                                }

                                $data = ldap_first_entry($ldap_server, $result);

                                if (!$data){
																	echo '<p id="wrongAccount" style="font-weight:500!important;">Wrong username and/or password, use your LDAP credentials!</p>';
																	return;
                                    throw new Exception('An error has occured during ldap_first_entry execution. Please check parameter of LDAP/checkLogin.');
                                }

                                $dn = ldap_get_dn($ldap_server, $data);

                                if (!$dn){
                                    throw new Exception('An error has occured during ldap_get_values execution (dn). Please check parameter of LDAP/checkLogin.');
                                }

                                $binded = ldap_bind($ldap_server, $dn, $password);

                                if($binded){
                                    $check_uname = "SELECT username FROM users WHERE BINARY username='$user'";
                                    $res = mysqli_query($conn, $check_uname);
                                    
                                    /* Create the new user */
                                    if(mysqli_num_rows($res) == 0){
                                        $create_user = "INSERT INTO users (username, credits) VALUES ('$user', '48')";
                                        mysqli_query($conn, $create_user);

                                        $_SESSION['username'] = $user;
                                        echo '<script>window.location = "calendar.php";</script>';
                                    }else{
                                    
                                        /* User exists */
                                        $sql_1 = "SELECT credits FROM users WHERE BINARY username='$user'";
                                        $result = mysqli_query($conn, $sql_1);
                                        $row = mysqli_fetch_assoc($result);
                                        $credits = $row['credits'];
                                        if (mysqli_num_rows($result) > 0) {
                                        	$_SESSION['username'] = $user;
                                        	echo '<script>window.location = "calendar.php";</script>';
                                        }
                                    }
                                }else{
                                    echo '<p id="wrongAccount" style="font-weight:500!important;">Wrong username and/or password, use your LDAP credentials!</p>';
                                }
                            }
                            ?> 

							<div class="col-xs-12 col-sm-7 form-lg">
								<div class="typeahead-container form-group form-icon-right">
									<label class="destination-search-3">Username</label>
									<div class="typeahead-field">
										<input name="username" type="text" class="form-control" required>
									</div>
									<i class="fa fa-user" aria-hidden="true"></i>
								</div>
							</div> 
							<div class="col-xs-12 col-sm-7 form-lg">
								<div class="typeahead-container form-group form-icon-right">
									<label class="destination-search-3">Password</label>
									<div class="typeahead-field">
										<input  name="password" type="password" class="form-control" required>
									</div>
									<i class="fa fa-key" aria-hidden="true"></i>
								</div>
							</div>
								<div class="btn-login">
																		<input type="submit" name="login" class="btn btn-primary btn-lg" value="&nbsp;&nbsp;Login&nbsp;" style="margin-right:20px;">
								</div>
						</form>
					</div>
				</div>
			</div>  
		</div>        
	</div>
</body>
<?php require_once('footer.php'); ?>
