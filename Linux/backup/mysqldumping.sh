#!/bin/sh

if [ "$1" = '' ]
then
echo DIR TO WRITE NEEDED >&2
exit
fi

USER=*****
PASS=****

BASES="$(mysql -u $USER -p$PASS -Bse 'show databases')"

for db in $BASES ;do

if [ $db != 'information_schema' ]
then

mysqldump --skip-extended-insert -u $USER -p$PASS --database $db > $1/$db

fi

done
