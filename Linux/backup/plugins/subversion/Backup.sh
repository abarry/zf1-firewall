
if [ "`whereis svnadmin | awk {'print $2'}`" = '' ] || [ "`whereis svnlook | awk {'print $2'}`" = '' ]
then
	echo 'svnlook or svnadmin does not exist' >&2
	appExit
fi

dir=`giveValueArg "--store"`

if [ "$dir" == '' ]
then
	dir="$ABSOLUTE_DIR/data/svn"
	mkdir "$dir" 2>/dev/null
fi

# Create tmp

mkdir "$ABSOLUTE_DIR/data/tmp" 2>/dev/null
mkdir "$ABSOLUTE_DIR/data/tmp/svn" 2>/dev/null

DIRS=`giveNoDashArgs`

if [ "$DIRS" = '' ]
then
	echo 'Args needed' >&2
	appExit
fi

OLD_IFS=$IFS
IFS=$'\n'

for i in $DIRS
do

	CLEVER_NAME=${i//'/'/'.'}
	CLEVER_NAME=${CLEVER_NAME/'.'/''}
	
	mkdir "$dir/$CLEVER_NAME" 2>/dev/null
	LIST_FILES_SVN=`ls "$dir/$CLEVER_NAME" | sort -n`
	
	for u in $LIST_FILES_SVN
	do
		LAST_FILE_SVN="$u"
	done
	if [ "$LAST_FILE_SVN" = '' ]
	then
		LAST_FILE_SVN='0'
	fi
	
	LAST_COMMIT_SVN=`svnlook youngest "$i"`
	if [ "$LAST_COMMIT_SVN" != "$LAST_FILE_SVN" ]
	then
		BEGIN_DUMP_AT=`expr $LAST_FILE_SVN + 1`
		END_DUMP_AT="$LAST_COMMIT_SVN"
		echo "Svn $i dumping between $BEGIN_DUMP_AT and $END_DUMP_AT"
		STACK=`seq $BEGIN_DUMP_AT $END_DUMP_AT`
		for v in $STACK
		do
			# $v is version to backup
			rm "$ABSOLUTE_DIR/data/tmp/svn/*" 2>/dev/null
			echo "Svn $i Dumping process revision $v"
			svnadmin dump --deltas -r $v --incremental $i > "$dir/$CLEVER_NAME/$v" 2>"$ABSOLUTE_DIR/data/tmp/svn/log"
			echo "Svn $i Dumping done revision $v"
			if [ -f "$ABSOLUTE_DIR/data/tmp/svn/log" ]
			then
				SVN_ERR=`grep -v '* Dumped' "$ABSOLUTE_DIR/data/tmp/svn/log"`
				if [ "$SVN_ERR" != '' ]
				then
					echo "$SVN_ERR" >&2
					appExit
				fi
				rm "$ABSOLUTE_DIR/data/tmp/svn/log"
			fi
		done
	else
		echo "No new revision for $i"
	fi

done

IFS=$OLD_IFS

echo "Backup processing ..."
SpiderOak --backup="$dir"
echo "Backup done !"

rm "$ABSOLUTE_DIR/data/tmp/svn/*" 2>/dev/null
rmdir "$ABSOLUTE_DIR/data/tmp/svn"