<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 29/12/16
 * Time: 2:14 PM
 */
class SlackNotification {

    private $message;
    private $channel;

    /**
     * @return mixed
     */
    public function getMessage() {

        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message) {

        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getChannel() {

        return $this->channel;
    }

    /**
     * @param mixed $channel
     */
    public function setChannel($channel) {

        $this->channel = $channel;

    }

    public function send() {

        $message = "payload=" . json_encode(
            array('text' =>
                $this->message));

        $c = curl_init($GLOBALS['slackWebhook']);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $message);
        $result = curl_exec($c);

        print_r($message);

        if (curl_error($c)) {

            addlog("Slack Notification", "Unable to send notification", "Error: " . curl_error($c) . " Result = " . $result . " Message: " . $this->message);

        } else {

            addlog("Slack Notification", "Sent notification", "Message: " . $this->message);

        }

        curl_close($c);

    }

}