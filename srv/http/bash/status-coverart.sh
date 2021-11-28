#!/bin/bash

readarray -t args <<< "$1"
artist=${args[0]}
album=${args[1]}
file=${args[2]}
type=${args[3]}
date=$( date +%s )
dirshm=/srv/http/data/shm
covername=$( echo $artist$album | tr -d ' "`?/#&'"'" )

coverFilesLimit() {
	/srv/http/bash/cmd.sh coverfileslimit
}
# already got path in temp file
[[ -e $dirshm/local/$covername ]] && cat $dirshm/local/$covername && exit
# already got embedded
[[ -e /srv/http/data/embedded/$covername.jpg ]] && echo /data/embedded/$covername.jpg && exit
# already got online
for ext in jpg png; do
	[[ -e $dirshm/online/$covername.$ext ]] && echo ${coverfile:0:-4}.$date.$ext && exit
done

# cover file
path="/mnt/MPD/$file"
[[ -f "$path" ]] && path=$( dirname "$path" ) # from status.sh as file
coverfile=$( ls -1X "$path"/cover.{gif,jpg,png} 2> /dev/null | head -1 )
[[ -z $coverfile ]] && coverfile=$( ls -1X "$path"/*.{gif,jpg,png} 2> /dev/null \
										| grep -i '/album\....$\|/folder\....$\|/front\....$' \
										| head -1 )
if [[ $coverfile ]]; then
	echo ${coverfile:0:-4}.$date.${coverfile: -3} | tee $dirshm/local/$covername
	coverFilesLimit
	exit
fi

# embedded
path="/mnt/MPD/$file"
dir=$( dirname "$path" )
filename=$( basename "$path" )
coverfile=/srv/http/data/embedded/$covername.jpg
kid3-cli -c "cd \"$dir\"" \
		-c "select \"$filename\"" \
		-c "get picture:$coverfile" &> /dev/null # suppress '1 space' stdout
if [[ -e $coverfile ]]; then
	echo /data/embedded/$covername.$date.jpg | tee $dirshm/local/$covername
	coverFilesLimit
	exit
fi

# online
killall status-coverartonline.sh &> /dev/null # new track - kill if still running
$dirbash/status-coverartonline.sh "$artist
$album" &> /dev/null &
