if [ `whoami` != 'root' ]
then
	echo 'Need to be root'
	echo 'Plugin can be lauched as other user : user must be througth on command arg'
	exit
fi

PLUGIN='$1'
shift

source "./plugins/$PLUGIN/Restore.sh"

#use --restore