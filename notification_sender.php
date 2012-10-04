<?php

/**
 * @author: Moustafa Makboul
 *
 * Notication sender class to retrieve pending notifications and send them
 *
 */
class Notification_sender {
    
    public $app_id;
    public $app_secret;
    private $_db;
        
    /*
     * Constructor connects to DB when new instance is created
     * on failure throws new exception
     * @param array $config database configuration settings, application settings
     */
    function __construct($config) {
        //new db connection
        $this->_db = new mysqli($config['db']['host'], $config['db']['user'], $config['db']['pass'], $config['db']['database']);
        //check connection
        if ($this->_db->connect_errno) {
            throw new Exception("Connect failed:".$this->_db->connect_error);
        }
        //configure application info
        $this->app_id = $config['app']['app_id'];
        $this->app_secret = $config['app']['app_secret'];
    }

    public function set_app_id($appID){
        $this->app_id = $appID;
    }
    
    public function get_app_id(){
        return $this->app_id;
    }
    
    public function set_app_secret($appSecret){
        $this->app_secret = $appSecret;
    }
    
    public function get_app_secret(){
        return $this->app_secret;
    }

    /*
     * Retrieve all pending notifications
     */
    public function get_all_pending_notifications() {
        $pendingNotifications = array();
        $query = "select notification_id, msg, date from notifications where status = 'pending'";
        if ($result = $this->_db->query($query)) {
            while ($row = $result->fetch_object()) {
                $pendingNotifications[] = $row;
            }
            $result->close();
        }
        return $pendingNotifications;
    }
    
    /*
     * Retrieve the latest pending notification
     */
    public function get_one_pending_notification() {
        $pendingNotification = array();
        $query = "select notification_id, msg, date from notifications where status = 'pending' limit 1";
        if ($result = $this->_db->query($query)) {
            if ($row = $result->fetch_object()) {
                $pendingNotification[] = $row;
            }
            $result->close();
        }
        return $pendingNotification;
    }

    /*
     * updated the status of a specific notification
     * @param $notID ID of notification to update
     * @param $status new status of notification
     */
    public function set_notification_status($notID, $status) {
        $query = "update notifications set status = '$status' where notification_id = '$notID'";
        $this->_db->query($query);
    }

    /*
     * send the notification to app users
     * @param $data a notification object 
     * @param array $appUsers array of user objects
     */
    public function send_notification($data, $appUsers, $logsEnabled=TRUE) {
        //counters to hold stats
        $goodCntr = 0;
        $badCntr = 0;
        
        //app access token used for auth when sending notification
        $app_access_token = $this->_get_app_token();

        foreach ($appUsers as $user) {
            $app_notification_url = "https://graph.facebook.com/" . $user->fb_code . "/notifications?"
                    . $app_access_token . "&template=" . urlencode($data->msg) . "&method=post";

            if ($result = @file_get_contents($app_notification_url)) {
                if($logsEnabled)
                    $this->_log_notification_status($data, $user, "success");
                $goodCntr++;
            } else {
                if($logsEnabled)
                    $this->_log_notification_status($data, $user, "failed");
                $badCntr++;
            }
        }
        
        $stats = array(
            "success" => $goodCntr,
            "fail" => $badCntr
            );

        return $stats;
    }

    /*
     * Updates statistics for the notification sent
     * @param $notID notification id to be updated
     * @param $stats success and failure statistice to be updated 
     */
    public function set_notification_stats($notID, $stats) {
        $query = "update notifications set success = '" . $stats['success'] . "', fail = '" . $stats['fail'] . "' where notification_id = '" . $notID ."'";
        $this->_db->query($query);
    }

    /*
     * Retrieve installed application users
     */
    public function get_app_users() {
        $appUsers = array();
        //$query = "select distinct fb_code from application_users where app_code = '".$this->app_id."' order by fb_code";
        $query = "select distinct fb_code from application_users";
        if ($result = $this->_db->query($query)) {
            while ($row = $result->fetch_object()) {
                $appUsers[] = $row;
            }
            $result->close();
        }
        return $appUsers;
    }

    /*
     * log a new entry per notification
     * @param $data a notification object 
     * @param $user a user object
     * @param $status status of the notification
     */
    protected function _log_notification_status($data, $user, $status) {
        $query = "insert into notifications_log (notification_code, fb_code, status, date) values ('" . $data->not_id . "','" . $user->fb_code . "','" . $status . "',NOW())";
        $this->_db->query($query);
    }

    /*
     * get app auth token from facebook
     */
    protected function _get_app_token() {
        $app_id = $this->app_id;
        $app_secret = $this->app_secret;
                
        $token_url = "https://graph.facebook.com/oauth/access_token?" .
                "client_id=" . $app_id .
                "&client_secret=" . $app_secret .
                "&grant_type=client_credentials";

        $app_access_token = file_get_contents($token_url);

        return $app_access_token;
    }
}