<?php
/**
 * @author: Moustafa Makboul
 *
 * Master notificaion sender to create instances of notification senders
 * and update notification statuses
 *
 **/

include "notification_sender.php";

//database configuration for new instance
$config = array(
    "db" => array(
        "host" => "DB_HOST_HERE",
        "user" => "DB_USER_HERE",
        "pass" => "DB_PASSWORD_HERE",
        "database" => "DB_NAME_HERE"),
    "app" => array(
        "app_id" => "YOUR_APP_ID",
        "app_secret" => "YOUR_APP_SECRET"
    )
);

try{
    //create a new instance 
    $nSender = new Notification_sender($config);
}  catch (Exception $e){
    //echo error and terminate script
    echo $e;
    exit;
}

//get the latest pending notification
$pendingNotifications = $nSender->get_one_pending_notification();

//if no notifications are pending to be processed no need to continue
if( count($pendingNotifications) == 0 )
{
        echo "no notifications pending at all";
        exit;
}

//process each pending notification
foreach( $pendingNotifications as $pendingNotification )
{
        //get the users associated with the merchant sending the notification
        $appUsers = $nSender->get_app_users();

        //update the notification to indicate being processed
        $nSender->set_notification_status($pendingNotification->notification_id, 'processing');

        //send the notification to all app users
        $stats = $nSender->send_notification($pendingNotification, $appUsers, FALSE);

        //update status based on feedback
        $notificationStatus = ( $stats['success'] != 0 ) ? "sent":"failed";
        $nSender->set_notification_status($pendingNotification->notification_id, $notificationStatus);

        //update stats based on feedback
        $nSender->set_notification_stats($pendingNotification->notification_id, $stats);        
}

