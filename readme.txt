DESCRIPTION
=====================
"Report Spammer" is a MyBB plugin to detect and report spammers using
web sites and services like StopForumSpam.com and WhatIsMyIPAddress.com.

Settings: 1
Database changes: none
Templates changed: member_profile_modoptions, modcp_editprofile
Templates added: report_spammer_display
Website: https://github.com/dandv/Report-Spammer-MyBB-plugin



INSTALLATION
=====================
Copy the contents of the `upload` folder to your MyBB directory.

In Admin CP => Plugins, Install & Activate.
Then go to Admin CP => Settings => Report Spammer (at the very bottom) and enter
your StopForumSpam.key. This is required only if you want to confirm or report
spammers. You can get a key from http://www.stopforumspam.com/signup.



DOCUMENTATION
=====================
This plugin is aimed at moderators who suspect a user may be spamming. The plugin will add a panel
to the "Edit this user in Mod CP" page, with information from StopForumSpam.com on the user's
username, e-mail, and IP addresses used. It will also do a reverse IP lookup and get the user's
hostname, ISP and organization from WhatIsMyIPAddress.com. Since many spammers use server farms
for bot accounts, ISPs that have "server" in their name (e.g. ubiquityservers.com) will be flagged.
Based on this information, plus other information from the user's profile and posts (if any), a
moderator can make a decision as to whether the user is a spammer or not.

Spammer accounts commonly:
* have a username that doesn't make sense in any language (e.g. "xrdwibf"), or is an unusual name
  (e.g. "Orville Cooksley")
* have as ISP a web hosting provider, often with "server" in their name
* have a signature that advertises some product or service

A button to report the spammer, or confirm the report if they had been reported already, is
provided. It will generate an AJAX request to your MyBB instance, so the moderator doesn't have
to leave the page.



CHANGELOG
=====================
1.0, 2011-07-23
  First version: detect spammers using whatismyipaddress.com reverse IP lookup and stopforumspam.com
  In use at http://forum.quantifiedself.com

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
