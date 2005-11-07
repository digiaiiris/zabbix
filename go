#!/bin/sh

clear

echo Pre-making...
aclocal
autoconf
autoheader
automake -a
automake
# Change ./configure options if needed
#./configure --with-mysql --prefix=/home/zabbix/zabbix --with-net-snmp

#rm -f config.guess config.sub depcomp install-sh missing

#cp /usr/share/automake-1.9/config.guess	config.guess
#cp /usr/share/automake-1.9/config.sub	config.sub
#cp /usr/share/automake-1.9/depcomp	depcomp
#cp /usr/share/automake-1.9/install-sh	install-sh
#cp /usr/share/automake-1.9/missing	missing

#cd ~zabbix
#rm -f zabbix.tgz
#tar cvzf zabbix.tgz zabbix
#exit
echo Configuring...
export CFLAGS="-Wall"
#export CFLAGS="-Wall -pedantic"
./configure --enable-agent --enable-server --with-mysql --prefix=`pwd`
echo Cleaning...
make clean
echo Making...
make
#echo Installing...
make install 2>>WARNINGS >/dev/null
