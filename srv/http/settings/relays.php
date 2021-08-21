<?php
$time = time();
$pins = [ 11, 12, 13, 15, 16, 18, 19, 21, 22, 23, 32, 33, 35, 36, 37, 38, 40 ];
$powerbutton = @file( '/etc/powerbutton.conf', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
if ( $powerbutton ) {
	foreach( $powerbutton as $p ) {
		$powerpins[] = explode( '=', $p )[ 1 ];
	}
	$pins = array_diff( $pins, $powerpins );
}
$optionpin = '';
foreach ( $pins as $p ) $optionpin.= '<option value='.$p.'>'.$p.'</option>';
$htmlpin = '';
$htmlname = '';
for ( $i = 1; $i < 5; $i++ ) {
	$htmlpin.= '<select id="pin'.$i.'" name="pin'.$i.'" class="pin">'.$optionpin.'</select>';
	$htmlname.= '<input id="name'.$i.'" name="name'.$i.'" type="text" class="name" placeholder="(no name)">';
$localhost = in_array( $_SERVER[ 'REMOTE_ADDR' ], ['127.0.0.1', '::1'] );
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>relays</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="msapplication-tap-highlight" content="no">
	<link rel="icon" href="/assets/img/icon.<?=$time?>.png">
	<style>
		@font-face {
			font-family: rern; font-display: block; font-style: normal; font-weight: normal;
			src: url( "/assets/fonts/rern.<?=$time?>.woff2" ) format( 'woff2' );
		}
	</style>
	<link rel="stylesheet" href="/assets/css/colors.<?=$time?>.css">
	<link rel="stylesheet" href="/assets/css/common.<?=$time?>.css">
	<link rel="stylesheet" href="/assets/css/selectric.<?=$time?>.css">
	<link rel="stylesheet" href="/assets/css/info.<?=$time?>.css">
	<link rel="stylesheet" href="/assets/css/settings.<?=$time?>.css">
	<link rel="stylesheet" href="/assets/css/relays.<?=$time?>.css">
</head>

<body>
<div class="head">
	<i class="page-icon fa fa-relays"></i><span class="title">GPIO RELAYS</span><a href="/settings.php?p=system"><i id="close" class="fa fa-times"></i></a>
</div>
<div class="container">
<br>
<img id="gpiosvg" src="/assets/img/gpio.<?=$time?>.svg">
<br>

<form id="relaysform">
<div class="column section" id="gpio">
	<div class="gpio-float-l">
		<div class="column" id="gpio-num">
			<span class="gpio-text"><i class="fa fa-gpiopins bl"></i> Pin</span>
			<?=$htmlpin?>
			<span class="gpio-text"><i class="fa fa-stopwatch yl"></i> Idle</span>
			<select id="timer" name="timer" class="timer"></select>
		</div>
		<div class="column" id="gpio-name">
			<span class="gpio-text"><i class="fa fa-tag bl"></i> Name</span>
			<input id="name1" name="name1" type="text" class="name" placeholder="(no name)">
			<input id="name2" name="name2" type="text" class="name" placeholder="(no name)">
			<input id="name3" name="name3" type="text" class="name" placeholder="(no name)">
			<input id="name4" name="name4" type="text" class="name" placeholder="(no name)">
			<span class="timer">&nbsp;min. to <i class="fa fa-power red"></i></span>
		</div>
	</div>
	<div class="gpio-float-r">
		<div class="column">
			<span class="gpio-text"><i class="fa fa-power grn"></i> On Sequence</span>
			<div id="on"></div>
		</div>
		<div class="column">
			<span class="gpio-text"><i class="fa fa-power red"></i> Off Sequence</span>
			<div id="off"></div>
			<br>
			<a id="undo" class="infobtn infobtn disabled"><i class="fa fa-undo"></i> Undo</a>
			<a id="save" class="infobtn infobtn-primary disabled"><i class="fa fa-save"></i> Save</a>
		</div>
	</div>
</div>
</form>

</div>

<script src="/assets/js/plugin/jquery-3.6.0.min.js"></script>
<script src="/assets/js/plugin/jquery.selectric.min.<?=$time?>.js"></script>
<script src="/assets/js/info.<?=$time?>.js"></script>
<script src="/assets/js/relays.<?=$time?>.js"></script>
	<?php if ( $localhost ) include '../keyboard.php';?>

</body>
</html>
