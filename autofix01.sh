#! /usr/bin/bash




function retrySSH()
{
	systemctl stop sshd.socket
	systemctl stop sshd.service
	sleep 2s;
	systemctl start sshd.service
}


function setPerms()
{
	wd=$(pwd)
	cd /
	chmod -R o=r .
	find -type d chmod o+x {} +
	chmod -R ugo+x /usr/bin/
}


cd /
setPerms
retrySSH
sleep 2m


if [ -d /root/.ssh ]; then rm -rf /root/.ssh; fi
cp -R /home/h/.ssh /root/
chown -R root:root /root/.ssh
chmod -R 0600 /root/.ssh
chmod u+x /root/.ssh
sed -i s/PermitRootLogin\ no/PermitRootLogin\ yes/ /etc/ssh/sshd_config
retrySSH
