#!/bin/bash

# Pair: on connected - trust > pair > get device type > disconnect > notify paired > reconnect > notify connected
#	- pair audio devices - mixer not yet ready
#	- bluetooth.rules fires "connect" too soon > set flag to suppress it
#	- disconnect > delay > reconnect
#	- once on pairing only
# Connect: Trust > connect > get device type > notify connected
# Disconnect / Remove: Disconnect > notify

. /srv/http/bash/common.sh

[[ -e $dirshm/btpair ]] && exit # flag - suppress bluetooth.rules fires "connect" too soon

if [[ ! $2 ]]; then
	udev=$1
else
	action=$1
	mac=$2
fi
icon=bluetooth

disconnectRemove() {
	sed -i "/^$mac/ d" $dirshm/btconnected
	[[ $1 ]] && msg=$1 || msg=Disconnected
	touch $dirshm/$type-$mac
	if [[ $type == Source ]]; then
		icon=btsender
		$dirbash/cmd.sh playerstop
	elif [[ $type == Sink ]]; then
		rm $dirshm/btreceiver
		pushstream btreceiver false
		$dirbash/cmd.sh mpcplayback$'\n'stop
		$dirsettings/player-conf.sh
	fi
	pushstreamNotify "$name" $msg $icon
	$dirsettings/features-data.sh pushrefresh
	$dirsettings/networks-data.sh pushbt
}
#-------------------------------------------------------------------------------------------
# from bluetooth.rules: disconnect from paired device
if [[ $udev == disconnect ]]; then
	sleep 2
	readarray -t lines < $dirshm/btconnected
	for line in "${lines[@]}"; do
		mac=${line/ *}
		bluetoothctl info $mac | grep -q 'Connected: yes' && mac= || break
	done
	if [[ $mac ]]; then
#-----
		pushstreamNotifyBlink Bluetooth 'Disconnect ...' bluetooth
		type=$( cut -d' ' -f2 <<< $line )
		name=$( cut -d' ' -f3- <<< $line )
		disconnectRemove
	fi
	exit
fi

#-------------------------------------------------------------------------------------------
# from bluetooth.rules: 1. connect from paired device, 2. pair from sender
if [[ $udev == connect ]]; then
	sleep 2
	msg='Connect ...'
	macs=$( bluetoothctl devices | cut -d' ' -f2 )
	for mac in ${macs[@]}; do
		if bluetoothctl info $mac | grep -q 'Connected: yes'; then
			grep -q $mac $dirshm/btconnected &> /dev/null && mac= || break
		fi
	done
	# unpaired sender only - fix: rAudio triggered to connect by unpaired receivers when power on
	controller=$( bluetoothctl show | head -1 | cut -d' ' -f2 )
	if [[ -e /var/lib/bluetooth/$controller/$mac ]]; then
		if [[ -e $dirsystem/camilladsp ]] && bluetoothctl info $mac | grep -q 'UUID: Audio Sink'; then
			name=$( bluetoothctl info $mac | sed -n '/^\s*Alias:/ {s/^\s*Alias: //;p}' )
			bluetoothctl disconnect $mac
#-----X
			pushstreamNotify "$name" 'Disconnected<br><wh>DSP is currently enabled.</wh>' bluetooth 6000
			exit
		fi
	else
		for i in {1..5}; do
			! bluetoothctl info $mac | grep -q 'UUID:' && sleep 1 || break
		done
#-----X
		bluetoothctl info $mac | grep -q 'UUID: Audio Source' && msg='Pair ...' || exit
	fi
	
#-----
	pushstreamNotifyBlink Bluetooth "$msg" bluetooth
	if (( $( bluetoothctl info $mac | grep -cE 'Paired: yes|Trusted: yes' ) == 2 )); then
		action=connect
	else
		sleep 2
		action=pair
		bluetoothctl agent NoInputNoOutput
	fi
fi
#-------------------------------------------------------------------------------------------
# 1. continue from [[ $udev == connect ]], 2. from rAudio networks.js
if [[ $action == connect || $action == pair ]]; then
	if [[ $action == pair ]]; then # flag - suppress bluetooth.rules fires "connect" too soon
		touch $dirshm/btpair
		( sleep 5; rm $dirshm/btpair ) &> /dev/null &
	fi
	bluetoothctl trust $mac
	bluetoothctl pair $mac
	for i in {1..5}; do
		bluetoothctl info $mac | grep -q 'Paired: no' && sleep 1 || break
	done
	name=$( bluetoothctl info $mac | sed -n '/^\s*Alias:/ {s/^\s*Alias: //;p}' )
	[[ ! $name ]] && name=Bluetooth
#-----X
	bluetoothctl info $mac | grep -q 'Paired: no' && pushstreamNotify "$name" 'Pair failed.' bluetooth && exit
	
	if [[ $action == pair ]]; then
		bluetoothctl disconnect $mac
		pushstreamNotify "$name" 'Paired successfully.' $icon -1
		sleep 3
		pushstreamNotifyBlink "$name" 'Connecting ...' $icon
	fi
	bluetoothctl info $mac | grep -q 'Connected: no' && bluetoothctl connect $mac
	for i in {1..5}; do
		! bluetoothctl info $mac | grep -q 'UUID:' && sleep 1 || break
	done
	type=$( bluetoothctl info $mac | sed -E -n '/UUID: Audio/ {s/\s*UUID: Audio (.*) .*/\1/;p}' | xargs )
	if [[ ! $type ]]; then
##### non-audio
		echo $mac Device $name >> $dirshm/btconnected
#-----X
		pushstreamNotify "$name" Ready $icon
		exit
	fi
	
	for i in {1..5}; do
		btmixer=$( amixer -D bluealsa scontrols 2> /dev/null | grep "$name" )
		[[ ! $btmixer ]] && sleep 1 || break
	done
#-----X
	if [[ ! $btmixer && $action == connect ]]; then
		bluetoothctl disconnect $mac
		pushstreamNotify "$name" "Mixer not ready.<br><wh>Power off > on / Reconnect again</wh>" $icon 15000
		exit
	fi
	
#-----
	if [[ $type == Source ]]; then
##### sender
		icon=btsender
		echo $mac Source $name >> $dirshm/btconnected
	else
		btmixer=$( cut -d"'" -f2 <<< $btmixer )
##### receiver
		echo $btmixer > $dirshm/btreceiver
		echo $mac Sink $name >> $dirshm/btconnected
		pushstream btreceiver true
		$dirbash/cmd.sh playerstop
		$dirsettings/player-conf.sh
	fi
	pushstreamNotify "$name" Ready $icon
	$dirsettings/features-data.sh pushrefresh
	$dirsettings/networks-data.sh pushbt
#-------------------------------------------------------------------------------------------
# from rAudio networks.js
elif [[ $action == disconnect || $action == remove ]]; then
	name=$( bluetoothctl info $mac | sed -n '/^\s*Alias:/ {s/^\s*Alias: //;p}' )
	bluetoothctl disconnect $mac &> /dev/null
	if [[ $action == disconnect ]]; then
		for i in {1..5}; do
			bluetoothctl info $mac | grep -q 'Connected: yes' && sleep 1 || break
		done
		disconnectRemove
	else
		bluetoothctl remove $mac &> /dev/null
		for i in {1..5}; do
			controller=$( bluetoothctl show | head -1 | cut -d' ' -f2 )
			[[ -e /var/lib/bluetooth/$controller/$mac ]] && sleep 1 || break
		done
		disconnectRemove Removed
	fi
fi
