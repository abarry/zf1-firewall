
# SpiderOak ROOT Running directly to files
# To minimize code
# To able to have backup files times, owner, group and chmod
# wich will be restore

DIRS="`giveNoDashArgs`"
OLD_IFS=$IFS
IFS=$'\n'

if [ "$DIRS" = '' ]
then
	echo 'Args needed' >&2
	appExit
fi

for i in $DIRS
do
	if [ ! -e "$i" ]
	then
		echo "$i is nota valid file or dir" >&2
		appExit
	fi
done

for i in $DIRS
do
	echo "Backup $i processing ..."
	SpiderOak --backup="$i"
	echo "Backup done !"
done

IFS=$OLD_IFS
