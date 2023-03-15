#!/bin/bash

alias=r1

. /srv/http/bash/addons.sh

# 20230317
rm -f $diraddons/addons-list.json /srv/http/settings/addons.php

if crontab -l | grep -q addonsupdates; then
	cron="00 01 * * * $dirsettings/addons-data.sh"
	current=$( crontab -l | grep -v addonsupdates )
	[[ $current ]] && cron+="
$current"
	echo "$cron" | crontab -
fi

rm -f $dirsystem/btoutputonly
[[ -e $dirmpdconf/bluetooth.conf && -e $dirmpdconf/output.conf ]] && touch $dirsystem/btoutputall

# 20230218
sed -E -i 's/(cursor=)true/\1yes/; s/(cursor=)false/\1no/' $dirsystem/localbrowser.conf &> /dev/null

[[ -d $dirsystem/scrobble.conf ]] && rm -rf $dirsystem/scrobble.conf
if [[ -e /boot/kernel7.img ]]; then
	if [[ ! -e /usr/bin/firefox ]]; then
		echo -e "$bar Switch Browser on RPi to Firefox ..."
		pacman -R --noconfirm chromium
		pacman -Sy --noconfirm firefox
		timeout 1 firefox --headless &> /dev/null
	fi
	! grep -q hdmi_force_hotplug=1 /boot/config.txt && echo hdmi_force_hotplug=1 >> /boot/config.txt
fi

# 20230212
if [[ -e /boot/kernel8.img && ! $( ls /etc/systemd/network/et* 2> /dev/null ) ]]; then
	sed 's/=en/=eth/' /etc/systemd/network/en.network > /etc/systemd/network/eth.network
fi

# 20230130
if ! grep -q onevent /etc/spotifyd.conf; then
	echo '[global]
bitrate = 320
onevent = "/srv/http/bash/spotifyd.sh"
use_mpris = false
backend = "alsa"
volume_controller = "alsa"' > /etc/spotifyd.conf
	$dirsettings/player-conf.sh
fi

# 20130129
file=/srv/http/settings/camillagui/backend/views.py
if [[ -e $file ]] && ! grep -q 'name == "mute"' $file; then
	sed -i -e '/cdsp.get_volume/ a\
    elif name == "mute":\
        config = cdsp.get_config()\
        mute = True if cdsp.get_mute() else False\
        volume = cdsp.get_volume()\
        result = {"config": config, "mute": mute, "volume": volume}\
        return web.json_response(result)\
        
' -e '/cdsp.set_volume/ a\
    elif name == "mute":\
        cdsp.set_mute(value == "true")
' $file
	file=$dircamilladsp/configs/camilladsp.yml
	! grep -q '\- Volume' $file && sed -i '/names:/ a\  - Volume' $file
fi

# 20130123
if [[ -e $dircamilladsp/configs/default_config.yml ]]; then
	file=$dircamilladsp/configs/camilladsp.yml
	! grep -q '\- Volume' $file && sed -i '/names:/ a\  - Volume' $file
	mv $dircamilladsp/{configs/,}default_config.yml
	rm $dircamilladsp/configs/active_config.yml
	ln -s $dircamilladsp/{configs/camilladsp,active_config}.yml
fi

#-------------------------------------------------------------------------------
installstart "$1"

rm -rf /srv/http/assets/{css,fonts,js}

getinstallzip

. $dirbash/common.sh
dirPermissions

[[ -e $dirsystem/color ]] && $dirbash/cmd.sh color
[[ ! -e /usr/bin/camilladsp ]] && rm -rf /srv/http/settings/camillagui

hash=?v=$( date +%s )
sed -E -i "s/(rern.woff2).*'/\1$hash'/" /srv/http/assets/css/common.css
sed -i "s/?v=.*/$hash';/" /srv/http/common.php

installfinish
#-------------------------------------------------------------------------------

# 20230224
if [[ -e $dirmpdconf/replaygain.conf ]]; then
	! grep -q mixer_type.*software $dirmpdconf/output.conf && $dirsettings/player-conf.sh
fi
