<!-- start Navbar (Header) -->
<nav class="navbar navbar-default navbar-fixed-top with-slicknav">
    <div class="container">
        <div class="navbar-header">
            <img style="width:215px; margin-top:5px; padding-right:10px;" src="./images/logo.png">
            <!--<a class="navbar-brand" href="#">RESERVATION SYSTEM</a>-->
        </div>					
        <div class="pull-right">
            <div class="navbar-mini">
                <ul class="clearfix">
                <a href="mailto:&subject=Reservation - Request" target="_top" style="float:left;">Send us an e-mail</a>    
                    <li class="user-action">
                        <a data-toggle="modal" href="calendar.php?logout" class="btn btn-primary btn-inverse">&nbsp;Logout <?php if(isset($_GET['logout'])){ session_destroy(); echo '<script>window.location.href="index.php"</script>'; } ?></a> 
                    </li>
                </ul>
            </div>
        </div>
        <!-- navbar start -->
        <div id="navbar" class="collapse navbar-collapse navbar-arrow pull-left">
            <ul class="nav navbar-nav" id="responsive-menu">
                <li><a class='text-danger' style="font-weight:normal!important; text-decoration:none; text-transform:none; cursor:default;">Welcome, <?php echo $_SESSION['username']; ?></a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
    <div id="slicknav-mobile">
    </div>
    <div class="breadcrumb-wrapper">
        <div class="container">
            <div class="row">
                <?php 
                    $conn = require_once('config.php');
                    $uname = $_SESSION['username'];
                    $sql = "SELECT credits FROM users WHERE BINARY username='$uname'";
                    $result = mysqli_query($conn, $sql);
                    $credits = mysqli_fetch_assoc($result);
                ?>
                <div style="float:right; margin-right:15px;">
                    <p style="font-size:12px;"><credits class="text-danger">Credits renewal:</credits> Every Monday!</p>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <ol class="breadcrumb">
                        <p style="font-size:12px;"><credits class="text-danger">Your credits:</credits> <?php echo $credits['credits']; ?>/48.0 - 1 Credit/Hour</p>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</nav>
