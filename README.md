## [Zabbix](http://www.zabbix.com/)

This repo is a git mirror of [https://github.com/zabbix/zabbix](https://zabbix.org/wiki/Get_Zabbix). For the latest development see the master branch.

*Contribution* (namely issues and patches) information can be found at the [Get involved!](https://www.zabbix.com/developers) page.

This repository has the following additional branches compared to the official https://github.com/zabbix/zabbix:

* This INFO branch
* iiris-master branch that follows the latest merged git tag (see below) and contains Iiris specific changes
* iiris-x.y.z tags that are tagged release versions containing Iiris specific changes


### Manual update procedure of iiris-master to a later git tag (here to version x.y.z):

```
git checkout iiris-master
git checkout -b x.y.z-update
git merge x.y.z
```

Next, make a pull request from x.y.z-update to iiris-master branch.


### Syncing this forked repository

First clone this forked repository:
```
git clone git@github.com:digiaiiris/zabbix.git
```

Change to that directory:
```
cd zabbix
```

Add the original repository as an upstream remote repository:
```
git remote add upstream https://github.com/ORIGINAL_OWNER/ORIGINAL_REPOSITORY.git
```

Verify that the remote repository has been added:
```
git remote -v
```

Fetch the branches and their respective commits from the upstream repository:
```
git fetch upstream
```

Check out your fork's master branch locally:
```
git checkout -b master origin/master
```

Merge the changes from upstream/master into your local master branch:
```
git pull upstream master
```

Remove the upstream remote:
```
git remote remove upstream
```

Verify the upstream remote repository has been removed:
```
git remote -v
```

Push changes to your fork's remote repository:
```
git push origin master
```
