if [ `whoami` != 'root' ]
then
	echo 'Need to be root' >&2
	echo 'Plugin can be lauched as other user : user must be througth on command arg' >&2
	exit
fi

PLUGIN="$1"
shift

MEFILE=`pwd`
MEFILE="$MEFILE/$0"
ABSOLUTE_DIR=`dirname "$MEFILE"`
source "$ABSOLUTE_DIR/inc/main.sh"

if [ "$PLUGIN" = "--help" ]
then
PLUGIN_NAME="$1"
if [ "$PLUGIN_NAME" = '' ]
then
	echo ''
	echo "Syntax : $0 plugin_name arg0 arg1 arg2"
	echo ''
	echo 'plugin_name : the name of wanted plugin'
	echo 'argX : Arg to plugin, dependant of plugin (no generic)'
	echo ''
	echo 'Plugin List :'
	echo '-------------'
	echo `getPluginsList`
	echo '-------------'
else
	showPluginFile "$PLUGIN_NAME" "help"
	echo ''
fi
	exit
fi

if [ "$PLUGIN" = '' ]
then
	echo 'Plugin name needed' >&2
	exit
fi

if [ "`checkLock`" = '1' ]
then
	echo 'Backup locked' >&2
	exit
fi

createLock

if [ "`checkLock`" != '1' ]
then
	echo 'Lock isnt, fail' >&2
	exit
fi

echo "$PLUGIN backup-plugin beginning at " `getFrDateTime` " ..."
loadPlugin "$PLUGIN" "Backup.sh"
echo "$PLUGIN backup-plugin done at " `getFrDateTime` " ..."

appExit
