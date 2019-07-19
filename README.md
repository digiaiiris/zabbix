## [Zabbix](http://www.zabbix.com/)

This repo is a git mirror of [https://github.com/zabbix/zabbix](https://zabbix.org/wiki/Get_Zabbix). For the latest development see the master branch.

*Contribution* (namely issues and patches) information can be found at the [Get involved!](https://www.zabbix.com/developers) page.

This repository has the following additional branches compared to the official https://github.com/zabbix/zabbix:

* This INFO branch
* iiris-master branch that follows the latest merged git tag (see below) and contains Iiris specific changes
* iiris-x.y.z tags that are tagged release versions containing Iiris specific changes

Manual update procedure of iiris-master to a later git tag (here to version x.y.z):

```
git checkout iiris-master
git checkout -b x.y.z-update
git merge x.y.z
```

Next, make a pull request from x.y.z-update to iiris-master branch.
