DESCRIPTION
=====================
"Report Spammer" is a MyBB plugin to detect and report spammers using
web sites and services like StopForumSpam.com and WhatIsMyIPAddress.com.

Settings: 1
Database changes: none
Templates changed: member_profile_modoptions, modcp_editprofile
Templates added: report_spammer_display



INSTALLATION
=====================
Copy the contents of the `upload` folder to your MyBB directory.

In Admin CP => Plugins, Install & Activate.
Then go to Admin CP => Settings => Report Spammer (at the very bottom) and enter
your StopForumSpam.key. This is required only if you want to confirm or report
spammers. You can get a key from http://www.stopforumspam.com/signup.



CHANGELOG
=====================
1.0, 2011-07-23
  First version: detect spammers based on stopforumspam.com data and whatismyipaddress.com reverse IP lookup
  In use on at http://forum.quantifiedself.com

1.1, 2011-07-27
  Added AJAX button to report a spammer or confirm the report to stopforumspam.com
  

  
LOCALIZATION
=====================
This plugin does not support localization on purpose. For the rationale of this
decision, please carefully read
http://wiki.dandascalescu.com/essays/english-universal-language

Patches that implement localization will only be accepted if acceptable
refutation of the essay linked above is provided.

You are, of course, permitted to implement localization for your instance of
the plugin; however, making publicly available a modification of the plugin
that implements localization is prohibited by terms of this license.

  

LICENSE
=====================
Author: Dan Dascalescu
Released under GPL v3 (http://www.gnu.org/licenses/gpl.html), with the following amendment:
"Redistribution of modifications that include localization is prohibited."
