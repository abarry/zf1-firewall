
PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

./SpiderOak-Backup.sh files '/var/www/prod/iml-maintenance.fr' '/var/www/prod/devlopnet.com' 1>>/var/log/Backup.log 2>&1
./SpiderOak-Backup.sh mysql --user='mysqldumper' --pass='G6hVJCs9D6VeEpUR' 1>>/var/log/Backup.log 2>&1
./SpiderOak-Backup.sh subversion '/var/www/svn/global-projects' 1>>/var/log/Backup.log 2>&1

if [ -f "/var/log/Backup.log" ]
then
	mail -s 'Backup report' hi.pascalous@yahoo.fr < "/var/log/Backup.log"
	rm "/var/log/Backup.log"
fi