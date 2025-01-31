<div id="gpiosvg" class="hide">
<?php include 'assets/img/gpio.svg';?>
</div>
<div id="divsystem" class="section">
<?php
$id_data = [
	  'audio'         => [ 'name' => 'Audio',             'sub' => 'aplay',       'setting' => false,    'status' => true ]
	, 'backup'        => [ 'name' => 'Backup',                                    'setting' => 'none' ]
	, 'bluetooth'     => [ 'name' => 'Bluetooth',         'sub' => 'bluez',                              'status' => true ]
	, 'hddsleep'      => [ 'name' => 'Hard Drive Sleep' ]
	, 'hdmi'          => [ 'name' => 'HDMI',              'sub' => 'hot plug',    'setting' => false ]
	, 'hostname'      => [ 'name' => 'Player Name',                               'setting' => 'none' ]
	, 'lcdchar'       => [ 'name' => 'Character LCD',     'sub' => 'HD44780' ]
	, 'mpdoled'       => [ 'name' => 'Spectrum OLED' ]
	, 'powerbutton'   => [ 'name' => 'Power Button',      'sub' => 'Power LED' ]
	, 'relays'        => [ 'name' => 'Relay Module' ]
	, 'restore'       => [ 'name' => 'Restore',                                   'setting' => 'none' ]
	, 'rotaryencoder' => [ 'name' => 'Rotary Encoder' ]
	, 'shareddata'    => [ 'name' => 'Shared Data',       'sub' => 'client',      'setting' => 'custom' ]
	, 'softlimit'     => [ 'name' => 'Custom Soft Limit', 'sub' => 'CPU throttling' ]
	, 'soundprofile'  => [ 'name' => 'Sound Profile',     'sub' => 'sysctl',                             'status' => true ]
	, 'tft'           => [ 'name' => 'TFT 3.5" LCD' ]
	, 'timezone'      => [ 'name' => 'Time Zone',         'sub' => 'timedatectl', 'setting' => 'custom', 'status' => true ]
	, 'usbautoupdate' => [ 'name' => 'Hotplug Update',                            'setting' => false ]
	, 'vuled'         => [ 'name' => 'VU LED' ]
	, 'wlan'          => [ 'name' => 'Wi-Fi',             'sub' => 'iw',                                 'status' => true ]
];

htmlHead( [ //////////////////////////////////
	  'title'  => 'System'
	, 'status' => 'system'
	, 'button' => [ 'power' => 'power' ]
	, 'help'   => i( 'power btn' ).' Power'
] );
?>
	<div id="systemlabel" class="col-l text gr">
			Version
		<br>Kernel
		<br>Hardware
		<br>SoC
		<br>CPU
	</div>
	<div id="systemvalue" class="col-r text"></div> 
	<div style="clear:both"></div>
	<pre id="codesystem" class="hide"></pre>
</div>
<div id="divstatus" class="section">
<?php
htmlHead( [ //////////////////////////////////
	  'title'  => 'Status'
	, 'status' => 'status'
	, 'button' => [ 'refresh' => 'refresh' ]
	, 'help'   => i( 'refresh btn' ).' Refresh every 10 seconds'
] );
?>
	<div id="statuslabel" class="col-l text gr">
			CPU Load
		<br>CPU Temp<wide>erature</wide></span>
		<br>Time
		<br>Up Time
		<div id="warning"><i class="i-warning yl"></i>&ensp;<wh>Warning</wh></div>
	</div>
	<div id="statustext" class="col-r text"></div>
	<div style="clear:both"></div>
	<div class="helpblock hide">
<wh>• CPU Load:</wh>
 · Average number of processes which are being executed and in waiting.
 · calculated over 1, 5 and 15 minutes.
 · Each one should not be constantly over 0.75 x CPU cores.
 
<wh>• Warnings:</wh> (if any)
 · Power supply voltage and throttled state (<a href="https://www.raspberrypi.com/documentation/computers/os.html#get_throttled">vcgencmd get_throttled</a>)<!--
--><a class="softlimitno">
	· 80-84°C: CPU cores throttled.
	· 85°C: CPU cores and GPU throttled.</a><!--
--><a class="softlimit">
	· 60°C: Optimized throttling CPU cores and GPU (Soft limit - 3B+ only)</a>
· RPi 4: Utilize <a href="https://github.com/raspberrypi/documentation/blob/develop/documentation/asciidoc/computers/raspberry-pi/frequency-management.adoc#using-dvfs">Dynamic Voltage and Frequency Scaling</a> (DVFS)
</div>
<?php
htmlSetting( [
	  'id'   => 'softlimit'
	, 'help' => 'Temperature level for CPU optimized throttling (default: 60°C)'
] );
?>
</div>
<div id="divstorage" class="section">
<?php
$uid = exec( 'id -u mpd' );
$gid = exec( 'id -g mpd' );
htmlHead( [ //////////////////////////////////
	  'title'  => 'Storage'
	, 'status' => 'storage'
	, 'button' => [ 'addnas' => 'plus-circle' ]
	, 'help'   => <<< EOF
{$Fi( 'plus-circle btn' )} Add network storage

 · USB drives  Will be found and mounted automatically.
 · Commands used by {$Fi( 'plus-circle btn' )} Add network storage:
<pre class="gr">
mkdir -p "/mnt/MPD/NAS/<wh>NAME</wh>"

<g># CIFS: no user - username=guest, no password - password=""</g>
mount -t cifs "//<wh>SERVER_IP</wh>/<wh>SHARENAME</wh>" "/mnt/MPD/NAS/<wh>NAME</wh>" \
      -o noauto,username=<wh>USER</wh>,password=<wh>PASSWORD</wh>,uid=$uid,gid=$gid,iocharset=utf8

<g># NFS:</g>
mount -t nfs "<wh>SERVER_IP</wh>:<wh>/SHARE/PATH</wh>" "/mnt/MPD/NAS/<wh>NAME</wh>" \
      -o defaults,noauto,bg,soft,timeo=5
</pre> · Windows shares without password: <c>net user guest /active:yes</c>
EOF
] );
?>
	<ul id="list" class="entries"></ul>
	<div class="helpblock hide"><?=( i( 'usbdrive btn' ).' '.i( 'networks btn' ).' Context menu' )?></div>
	<pre id="codehddinfo" class="hide"></pre>
<?php
htmlSetting( [
	  'id'       => 'hddsleep'
	, 'disabled' => 'HDD not support sleep'
	, 'help'     => 'Sleep timer for USB hard drives.'
] );
htmlSetting( [
	  'id'   => 'usbautoupdate'
	, 'help' => 'Auto update Library database on insert/remove USB drives.'
] );
echo '</div>';
if ( file_exists( '/srv/http/data/shm/onboardwlan' ) ) {
// ----------------------------------------------------------------------------------
$head = [ //////////////////////////////////
	  'title'  => 'On-board Devices'
];
$body = [
	[
		  'id'       => 'audio'
		, 'disabled' => 'No other audio devices available.'
		, 'help'     => <<< EOF
 · For 3.5mm jack and HDMI audio output
 · Should not be disabled if there's no other DAC permanently installed.
EOF
	]
	, [
		  'id'   => 'bluetooth'
		, 'help' => <<< EOF
{$Fi( 'gear btn' )}
■ Sampling 16bit - Bluetooth receivers with fixed sampling
EOF
	]
	, [
		  'id'   => 'hdmi'
		, 'help' => <<< EOF
 · Force enable HDMI without connecting before boot
 · Enable if not detected properly
 · Should be disabled if not used.
EOF
	]
	, [
		  'id'       => 'wlan'
		, 'disabled' => 'js'
		, 'help'     => <<< EOF
{$Fi( 'gear btn' )}
Country of Wi-Fi regulatory domain:
	· <code>00</code> Least common denominator settings, channels and transmit power are permitted in all countries.
	· The connected router may override it to a certain country.
■ Auto start Access Point - On failed connection or no router
EOF
	]
];
htmlSection( $head, $body, 'onboard' );
// ----------------------------------------------------------------------------------
}
$head = [ //////////////////////////////////
	  'title' => 'GPIO Devices'
];
$body = [
	[
		  'html' => <<< EOF
<div id="divi2s">
	<div class="col-l single">Audio - I²S<i class="i-i2smodule"></i></div>
	<div class="col-r">
		<div id="divi2smodulesw">
			<input id="i2smodulesw" type="checkbox">
			<div class="switchlabel" for="i2smodulesw"></div>
		</div>
		<div id="divi2smodule">
			<select id="i2smodule"></select>
			<i id="setting-i2smodule" class="i-gear setting"></i>
		</div>
		<span class="helpblock hide"><!--
-->I²S DAC/audio HAT(Hardware Attached on Top) for audio output.
 · HAT with EEPROM could be automatically detected.
 · See  if it's already set: {$Ftab( 'player', 'Player' )}<a class="helpmenu label">Output · Device </a>
{$Fi( 'gear btn' )}
Option to disable I²S EEPROM read for HAT with obsolete EEPROM
		</span>
	</div>
</div>
EOF
	]
	, [
		  'id'   => 'lcdchar'
		, 'help' => <<< EOF
<a class="img" data-name="lcdchar">LCD module</a> - display playback data
 · Support 16x2 and 20x4 LCD modules.
 · {$Fi( 'warning yl' )} LCD with I²C backpack must be modified: <a class="img" data-name="i2cbackpack">5V to 3.3V I²C and 5V LCD</a>
EOF
	]
	, [
		  'id'   => 'powerbutton'
		, 'help' => <<< EOF
<a class="img" data-name="powerbutton">Power button and LED</a> - power on/off rAudio
{$Fi( 'gear btn' )}
 · On - Fixed to pin <code>5</code>
 · Off - Default: pin <code>5</code> (single pin on+off)
 · If pin <code>5</code> is used by DAC or LCD, set 2 unused pins for:
	 · Off - Default: pin <code>7</code>
	 · Reserved - Default: pin <code>29</code>
EOF
	]
	, [
		  'id'   => 'relays'
		, 'help' => <<< EOF
<a class="img" data-name="relays">Relay module</a> - power on/off peripheral equipments
On/Off: {$Fmenu( 'raudio', 'System', 'relays' )}
 · More info: <a href="https://github.com/rern/R_GPIO/blob/master/README.md">+R GPIO</a>
 · Can be enabled and run as a test without a connected relay module.
EOF
	],
	[
		  'id'   => 'rotaryencoder'
		, 'help' => <<< EOF
<a class="img" data-name="rotaryencoder">Rotary encoder</a> for:
 · Turn volume up/down
 · Push to play/pause
EOF
	]
	,[
		  'id'   => 'mpdoled'
		, 'help' => '<a class="img" data-name="mpdoled">OLED module</a> - display audio level spectrum'
	]
	, [
		  'id'    => 'tft'
		, 'exist' => file_exists( '/etc/systemd/system/localbrowser.service' )
		, 'help'  => '<a class="img" data-name="lcd">TFT LCD module</a> with resistive touchscreen - local display'
	]
	, [
		  'id'   => 'vuled'
		, 'help' => <<< EOF
<a class="img" data-name="vuled">7 LEDs</a> - display audio level
 · <bl id="ledcalc">LED resister calculator</bl>
EOF
	]
];
htmlSection( $head, $body, 'gpio' );
$head = [ 'title' => 'Environment' ]; //////////////////////////////////
$body = [
	[
		  'id'    => 'hostname'
		, 'input' => '<input type="text" id="hostname" readonly>'
		, 'help'  => <<< EOF
For:
 · Access point, AirPlay, Bluetooth, SnapCast, Spotify, UPnP
 · Web Interface URL: <c id="avahiurl"></c>
 · System hostname
EOF
	]
	, [
		  'id'    => 'timezone'
		, 'input' => '<select id="timezone"></select>'
		, 'help'  => <<< EOF
{$Fi( 'gear btn' )}
Servers for time sync and package mirror
EOF
	]
	, [
		  'id'   => 'soundprofile'
		, 'help' => <<< EOF
Tweak kernel parameters to improve sound quality.
{$Fi( 'gear btn' )}
Swapiness (default: <code>60</code>)
	· Balance between swap disk vs system memory cache
	· Low - less swap
Maximum Transmission Unit (default: <code>1500</code> bytes)
	· Maximum size of one packet that can be transmitted in a network
	· High - less overhead more efficiency
	· Low - less delay
Transmit Queue Length (default: <code>1000</code>)
	· Number of packets allowed per kernel transmit queue in a network
	· High - improve performance under high load
EOF
	]
];
htmlSection( $head, $body, 'environment' );
$head = [ 'title' => 'Data and Settings' ]; //////////////////////////////////
$body = [
	[
		  'id'   => 'backup'
		, 'help' => <<< EOF
Backup all data and settings:
 · Library: Database, Bookmarks, DAB Radio, Web Radio
 · Playback: Lyrics
 · Playlist: Audio CD, Saved playlists
 · Settings
EOF
	]
	, [
		  'id'   => 'restore'
		, 'help' => <<< EOF
 · Restore all data and settings from a backup file.
 · Reset to default - Reset everything except Wi-Fi connection and custom LAN
EOF
	]
	, [
		  'id'       => 'shareddata'
		, 'disabled' => labelIcon( 'Server rAudio', 'rserver' ).' is currently active.'
		, 'help'     => <<< EOF
Connect shared data as client for:
 · Library database
 · Data - Audio CD, bookmarks, lyrics, saved playlists and Web Radio
 · Display order of Library home

Note:
 · SSH password must be default.
 · Enabled - {$Fi( 'microsd btn' )} SD and {$Fi( 'usbdrive btn' )} USB:
	 · Moved to <c>/mnt/SD</c> and <c>/mnt/USB</c>
	 · Not availble in Library home

 • <wh>rAudio as server:</wh> (Alternative 1)
	Server: {$Ftab( 'features', 'Features' )}{$FlabelIcon( 'Server rAudio', 'rserver' )}
	Clients: {$FlabelIcon( 'Shared Data', 'networks' )} Type ● rAudio
	
 • <wh>Other servers:</wh> (Alternative 2)
	Server: Create a share for data with full permissions
	 · Linux:
		NFS: <c>777</c>
		CIFS (SMB): <c>read only = no</c>
	 · Windows:
		Right-click Folder &raquo; Properties &raquo; 
			<btn>Sharing</btn> &raquo; <btn>Advanced Sharing...</btn> &raquo; <btn>Permissions</btn>
				Everyone - Full Control
			<btn>Security</btn>
				Everyone - Full Control
	Clients:
	 · {$FlabelIcon( 'Shared Data', 'networks' )} Add the created share
	 · Data on 1st connected client will be used as initial shared.
EOF
	]
];
htmlSection( $head, $body, 'datasetting' );
$listui = [
	[
	    'HTML5-Color-Picker'
	  , 'A scaleable color picker implemented using HTML5'
	  , 'https://github.com/NC22/HTML5-Color-Picker'
	],[
	    'Inconsolata font'
	  , 'A monospace font designed for printed code listings and the like'
	  , 'https://fonts.google.com/specimen/Inconsolata'
	],[
	    'Lato-Fonts'
	  , 'A san-serif typeface family'
	  , 'http://www.latofonts.com/lato-free-fonts'
	],[
	    'lazysizes'
	  , 'Lazy loader for images'
	  , 'https://github.com/aFarkas/lazysizes'
	],[
	    'pica'
	  , 'Resize image in browser with high quality and high speed'
	  , 'https://github.com/nodeca/pica'
	],[
	    'QR Code generator'
	  , 'QR Code generator'
	  , 'https://github.com/datalog/qrcode-svg'
	],[
	    'roundSlider'
	  , 'A plugin that allows the user to select a value or range of values.'
	  , 'https://github.com/soundar24/roundSlider'
	],[
	    'simple-keyboard'
	  , 'Virtual Keyboard'
	  , 'https://github.com/hodgef/simple-keyboard'
	],[
	    'Select2'
	  , 'A replacement for select boxes'
	  , 'https://github.com/select2/select2'
	],[
	    'Sortable'
	  , 'Reorderable drag-and-drop lists'
	  , 'https://github.com/SortableJS/Sortable'
	]
];
$uihtml     = '';
foreach( $listui as $ui ) {
	$uihtml.= '<a href="'.$ui[ 2 ].'">'.$ui[ 0 ].'</a> - '.$ui[ 1 ].'<br>';
}
$hdparmhide = ! file_exists( '/usr/bin/hdparm' ) ? ' style="display: none"' : '';
$indexhtml  = '';
for( $i = 'A'; $i !== 'AA'; $i++ ) {
	$indexhtml.= '<a>'.$i.'</a>';
	if ( $i === 'M' ) $indexhtml.= '<br class="brindex">';
}
?>
<div id="divabout" class="section">
	<a href="https://github.com/rern/rAudio/discussions"><img src="/assets/img/icon.svg<?=$hash?>" style="width: 40px"></a>
	<div id="logotext">rAudio
	<br><gr>b y&emsp;r e r n</gr></div>
	
	<heading class="subhead">Back End</heading>
	<div class="list">
		<a href="https://www.archlinuxarm.org">Arch Linux Arm</a>
		<p>Arch Linux for ARM processors which aims for simplicity and full control to the end user.</p>
	</div>
	<div class="listtitle backend">Packages:</i>
	<br><?=$indexhtml?></div>
	<div class="list"></div>
	
	<heading class="subhead">Front End</heading>
	<div class="list">
		<a href="https://nginx.org/en/">nginx</a>
		<p>HTTP and reverse proxy server, a mail proxy server, and a generic TCP/UDP proxy server</p>
		<a href="https://www.php.net">PHP</a>
		<p>PHP: Hypertext Preprocessor - A scripting language for web server side</p>
		<a href="https://whatwg.org">HTML</a>
		<p>Hypertext Markup Language for displaying documents in web browsers</p>
		<a href="https://www.w3.org/TR/CSS">CSS</a>
		<p>Cascading Style Sheets for describing the presentation of HTMLs</p>
		<a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript">JavaScript</a>
		<p>A scripting language for working with HTML Document Object Model(DOM) on client side</p>
		<a href="https://jquery.com/">jQuery</a>
		<p>A JavaScript library for simplifying HTML DOM tree traversal and manipulation</p>
	</div>
	<div class="listtitle">Javascript Plugins: <?=i( 'chevron-down bl' )?></div>
	<div class="list hide"><?=$uihtml?></div>
	
	<heading class="subhead">Data</heading>
	<div class="list">
		<a href="https://www.last.fm">last.fm</a>
		<p>Coverarts and artist biographies</p>
		<a href="https://webservice.fanart.tv">fanart.tv</a>
		<p>Artist images and fallback coverarts</p>
		<a href="https://radioparadise.com">Radio Paradise</a> <a href="https://www.fip.fr/">Fip</a> <a href="https://www.francemusique.fr/">France Musique</a>
		<p>Coverarts for their own stations</p>
		<a href="http://gnudb.gnudb.org">GnuDB</a>
		<p>Audio CD track list</p>
	</div>
</div>

<div id="menu" class="menu hide">
<a class="info"<?=$hdparmhide?>><?=i( 'info-circle' )?>Info</a>
<a class="forget"><?=i( 'minus-circle' )?>Forget</a>
<a class="remount"><?=i( 'check' )?>Re-mount</a>
<a class="unmount"><?=i( 'close' )?>Unmount</a>
</div>
