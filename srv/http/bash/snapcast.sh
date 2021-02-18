#!/bin/bash

dirtmp=/srv/http/data/shm
snapserverfile=$dirtmp/snapserverip
snapclientfile=$dirtmp/snapclientip

pushstream() {
	curl -s -X POST http://$1/pub?id=snapcast -d "$2"
}

if [[ $1 == start ]]; then # client start - save server ip
	mpc -q stop
	systemctl start snapclient
	sleep 2
	serverip=$( journalctl -u snapclient | grep 'Connected to' | tail -1 | awk '{print $NF}' )
	if [[ -n $serverip ]]; then
		mv $dirtmp/player-{*,snapclient}
		echo $serverip > $snapserverfile
		clientip=$( ifconfig | awk '/inet .*broadcast/ {print $2}' )
		pushstream $serverip '{ "add": "'$clientip'" }'
		systemctl try-restart shairport-sync spotifyd upmpdcli &> /dev/null
	else
		systemctl stop snapclient
		echo -1
	fi
elif [[ $1 == stop ]]; then # client stop - delete server ip, curl remove client ip
	systemctl stop snapclient
	mv $dirtmp/player-{*,mpd}
	curl -s -X POST http://127.0.0.1/pub?id=mpdplayer -d "$( /srv/http/bash/status.sh )"
	serverip=$( cat $snapserverfile )
	clientip=$( ifconfig | awk '/inet .*broadcast/ {print $2}' )
	rm $snapserverfile
	pushstream $serverip '{ "remove": "'$clientip'" }'
elif [[ $1 == add ]]; then # connected from client - save client ip
	clientip=$2
	! grep -q $clientip $snapclientfile 2> /dev/null && echo $clientip >> $snapclientfile
elif [[ $1 == remove ]]; then # disconnected from client - remove client ip
	clientip=$2
	sed -i "/$clientip/ d" $snapclientfile
elif [[ $1 == serverstop ]]; then # force clients stop
	snapclientfile=$dirtmp/snapclientip
	if [[ -e $snapclientfile ]]; then
		sed -i '/^$/d' $snapclientfile # remove blank lines
		if [[ -s $snapclientfile ]]; then
			readarray -t clientip <<< $snapclientfile
			for ip in "${clientip[@]}"; do
				pushstream $ip -1
			done
		fi
		rm -f $snapclientfile
	fi
fi