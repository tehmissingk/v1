<?php
    if(isloggedin())
        redirect ($CFG->wwwroot);
    
    echo $OUTPUT->doctype();
    global $errormsg, $valid_ping, $valid_unlock;
?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en" <?php echo $OUTPUT->htmlattributes(); ?>> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en" <?php echo $OUTPUT->htmlattributes(); ?>> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en" <?php echo $OUTPUT->htmlattributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en" <?php echo $OUTPUT->htmlattributes(); ?>> <!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $PAGE->title; ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <?php echo $OUTPUT->loadGoogleFont(); ?>
    
    <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>">
    
    <?php echo $OUTPUT->googleAnalytics() ?>
    <?php echo $OUTPUT->standard_head_html() ?>
    
    <noscript>
        <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot;?>/theme/genesis/css/nojs.css" />
    </noscript>
</head>
<body id="<?php p($PAGE->bodyid) ?>">
    <?php echo $OUTPUT->standard_top_of_body_html(); ?>
    <?php echo "<div style='display: none;'>".$OUTPUT->main_content()."</div>"; ?>
    <?php include 'header.php'; ?>
    
    <div id="contentarea" class="row">
        <div class="sklt-container">
            <div class="sixteen columns">
                <br>
                <center>
                    <a href="<?php echo $CFG->wwwroot; ?>">
                        <?php echo $OUTPUT->logo(); ?>
                    </a>
                </center>
                <br>
            </div>
        </div>
        <div class="sklt-container" id="loginContainer">
            <div class="sixteen columns">
                <div class="loginbox">
                    <form method="post"  action="<?php echo $CFG->wwwroot; ?>/login/unlock.php">
						<?php if ($valid_ping AND $valid_unlock) { ?>
                        <div class="leftarea">
                            <p>Your system has been unlocked.</p>
                            <div class="clear"></div>
							<div class="form-group">
								<div><a href="index.php">Return to Home</a></div>
							</div>
                        </div>
						<?php } else { ?>
                        <div class="leftarea">
                            <p>Unlock Code</p>
                            <div class="clear"></div>
							<div class="form-group">
								<input type="text" name="unlockcode" size="40" class="form-control"/>
								<div><a href="index.php">Return to Home</a></div>
							</div>
                        </div>
                        <input type="submit" value=">"/>
						<?php } ?>
                    </form>

                    <?php if(isset($errormsg) && trim($errormsg) != ""){ ?>
                        <div class="error">
							<?php 
								echo $errormsg;
							?>
                        </div>
                    <?php } else if (!$valid_ping OR !$valid_unlock) { ?>
                        <div class="error">
							System Locked
                        </div>
                    <?php } ?>
                </div>
                <br>
                <div class="shadow2"></div>
                <br><br>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
<?php 
    echo $OUTPUT->standard_end_of_body_html();
    echo $OUTPUT->forcefooter();
?>