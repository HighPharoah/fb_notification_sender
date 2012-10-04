fb_notification_sender
======================

Overview:
=========
A database driven command line tool used to send notifications on Facebook

Description:
============
When Facebook first launched their platform, applications were able to send notifications to all their users. This was a very powerful communication channel and for some reason Facbook removed it completely and came up with requests instead. Now they've brought it back, althought still in "beta" the Notifications API is usable.<br/>
You can find out more about the Notifications API here: https://developers.facebook.com/docs/app_notifications/<br/>

The Notification_sender class provides a wrapper around this API and provides some other useful methods for stats and logs.<br/>
sender_instance.php provides an example usage so you can follow along.<br/>

WHAT YOU SHOULD KNOW:<br/>
1- The Notification_sender is database driven, pulling pending notifications and application users from appropriate tables. If you choose to enable logging a table needs to exist to hold that info<br/>
2- The database settings and app credentials are passed to the constructor via a $config param. see sender_instance.php 
3- The minimum expected tables are:<br/>
notifications (notification_id, msg, success, fail, date)<br/>
application_users (user_ID, fb_code)<br/>
notifications_log (log_ID, notification_code, fb_code, status, date)<br/>

WHAT YOU SHOULD DO:<br/>
1- Update the $config param with your data<br/>
2- Let me know if something is not working or needs clarification<br/>
3- Enjoy<br/>




 

