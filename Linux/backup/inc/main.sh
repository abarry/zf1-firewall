if [ "`whereis SpiderOak | awk {'print $2'}`" = '' ]
then
	echo 'SpiderOak does not exist' >&2
	exit
fi

ARGUMENTS=''
ARGS_SIZE='0'

for i in "$@"
do  
	ARGUMENTS[$ARGS_SIZE]="$i"
	ARGS_SIZE=`expr $ARGS_SIZE + 1`
done


function getLeftArg
{
	ARG="$1"
	LEFT=${ARG%%=*}
	echo "$LEFT"
}

function giveValueArg
{

	ARG_NAME="$1"
	
	for i in "${ARGUMENTS[@]}"
	do  
		LEFT=`getLeftArg "$i"`
		if [ "$LEFT" = "$ARG_NAME" ]
		then
			RIGHT=`getRightArg "$i"`
			echo "$RIGHT"
			return 1
		fi
	done

}

function giveNoDashArgs
{

	for i in "${ARGUMENTS[@]}"
	do  
		LEFT=`getLeftArg "$i"`
		if [ `isDoubleDash "$LEFT"` = '0' ] && [ `isSimpleDash "$LEFT"` = '0' ]
		then
			echo "$i"
		fi
	done
}

function giveBoolArg
{
	ARG_NAME="$1"
	
	for i in "${ARGUMENTS[@]}"
	do  
		if [ "$i" = "$ARG_NAME" ]
		then
			echo "1"
			return 1
		fi
	done
	
	echo "0"
}

function isDoubleDash
{
	ARG="$1"
	if [ "${ARG:0:2}" = '--' ]
	then
		echo '1'
	else
		echo '0'
	fi
}

function isSimpleDash
{
	ARG="$1"
	if [ "${ARG:0:1}" = '-' ] && [ "${ARG:0:2}" != '--' ]
	then
		echo '1'
	else
		echo '0'
	fi
}

function sanitizeDash
{
	ARG="$1"
	ARG_SIZE=${#ARG}
	ARG_SIZE_SIMPLE=`expr $ARG_SIZE - 1`
	ARG_SIZE_DOUBLE=`expr $ARG_SIZE - 2`
	if [ `isSimpleDash "$ARG"` = '1' ]
	then
		echo "${ARG:1:$ARG_SIZE_SIMPLE}"
	elif [ `isDoubleDash "$ARG"` = '1' ]
	then
		echo "${ARG:2:$ARG_SIZE_DOUBLE}"
	else
		echo "$1"
	fi
}

function getRightArg
{
	ARG="$1"
	RIGHT=${ARG#*=}
	echo "$RIGHT"
}

function hasNoReadable
{
	DIR="$1"
	LISTE=`find $DIR/*`
	OLD_IFS=$IFS
	IFS=$'\n'
	for i in "$LISTE"
	do
		if [ ! -r "$i" ]
		then
			echo "$i must be readable"
		fi
		if [ -d "$i" ]
		then
			if [ ! -x "$i" ]
			then
				echo "$i must be executable"
			fi
		fi
	done
	IFS=$OLD_IFS
}

function changeModOwnIfNot
{
	DIR="$1"
	OWN="$2"
	GROUP="$3"
	MOD="$4"
	
	if [ `stat --format="%U:%G:%a" "$DIR"` != "$OWN:$GROUP:$MOD" ]
	then
		chown $OWN:$GROUP "$DIR"
		chmod $MOD "$DIR"
	fi
}

function isEmptyFile
{
	FILE="$1"
	if [ `wc -m "$FILE" | awk {'print $1'}` = "0" ]
	then
		echo "1"
	else
		echo "0"
	fi
}

function appExit
{
	rm "$ABSOLUTE_DIR/.lock" 2>/dev/null
	exit
}

function createLock
{
	echo '' > "$ABSOLUTE_DIR/.lock"
}

function checkLock
{
	if [ -f "$ABSOLUTE_DIR/.lock" ]
	then
		echo "1"
	else
		echo "0"
	fi
}

function testPlugin
{
	PLUGIN_NAME="$1"
	FILE_NAME="$2"
	if [ -f "$ABSOLUTE_DIR/plugins/$PLUGIN_NAME/$FILE_NAME" ]
	then
		echo '1'
	else
		echo '0'
	fi
}

function loadPlugin
{
	PLUGIN_NAME="$1"
	FILE_NAME="$2"
	if [ `testPlugin "$PLUGIN_NAME" "$FILE_NAME"` != '1' ]
	then
		echo "Plugin $PLUGIN file $FILE_NAME not found" >&2
		appExit
	fi
	source "$ABSOLUTE_DIR/plugins/$PLUGIN_NAME/$FILE_NAME"
}

function showPluginFile
{
	PLUGIN_NAME="$1"
	FILE_NAME="$2"
	if [ `testPlugin "$PLUGIN_NAME" "$FILE_NAME"` != '1' ]
	then
		echo "Plugin $PLUGIN file $FILE_NAME not found" >&2
		appExit
	fi
	cat "$ABSOLUTE_DIR/plugins/$PLUGIN_NAME/$FILE_NAME"
}

function getPluginsList
{
	echo `ls -l "$ABSOLUTE_DIR/plugins/" | awk {'print $9'}`
}

function getFrDateTime
{
	echo `date +"%d/%m/%Y %H:%M:%S"`
}

