
if [ "`whereis mysql | awk {'print $2'}`" = '' ]
then
	echo 'MySQL does not exist' >&2
	appExit
fi

dir=`giveValueArg "--store"`
user=`giveValueArg "--user"`
pass=`giveValueArg "--pass"`

if [ "$dir" == '' ]
then
	dir="$ABSOLUTE_DIR/data/mysql"
	mkdir "$dir" 2>/dev/null
fi

# Create tmp

mkdir "$ABSOLUTE_DIR/data/tmp" 2>/dev/null
mkdir "$ABSOLUTE_DIR/data/tmp/mysql" 2>/dev/null

# Backup generate
BASES=`mysql -u "$user" -p"$pass" -Bse 'show databases'`
for db in $BASES ;do
	if [ "$db" = 'information_schema' ]
	then
		echo "$db ignore"
	else
		echo "Temporary mysql dumping $db"
		mysqldump --skip-extended-insert -u "$user" -p"$pass" --database "$db" | grep -v -e "^-- Dump completed on " > "$ABSOLUTE_DIR/data/tmp/mysql/$db"
	fi
done

echo "Synchronisation temp to data ..."
rsync --recursive --verbose --progress --checksum --delete "$ABSOLUTE_DIR/data/tmp/mysql/" "$dir/"
# --checksum problème rencontré, pas de solutions trouvées, je passe
# Finalement checksum n'était pas en cause, mais la date insérée par mysqldump (donnant la même taille fichier)

# for i in $ABSOLUTE_DIR/data/tmp/mysql/*
# do
	# DOTRANSFERT='1'
	# BASENAME=`basename $i`
	# if [ -e "$dir/$BASENAME" ]
	# then
		# COMP="`cmp "$i" "$dir/$BASENAME" 2>&1`"
		# echo "Compare ? Result $COMP"
		# if [ "$COMP" = '' ]
		# then
			# DOTRANSFERT='0'
			# echo 'Compare tmp to data : no change !'
		# fi
	# else
		# echo 'New file, welcome !'
	# fi
	
	# if [ "$DOTRANSFERT" = '0' ]
	# then
		# echo 'Sync OFF'
	# else
		# echo 'Sync ON'
		# cp "$i" "$dir/$BASENAME"
	# fi
	
# done

echo "Synchronisation done !"

rm "$ABSOLUTE_DIR/data/tmp/mysql/"*
rmdir "$ABSOLUTE_DIR/data/tmp/mysql"

echo "Backup processing ..."
SpiderOak --backup="$dir"
echo "Backup done !"