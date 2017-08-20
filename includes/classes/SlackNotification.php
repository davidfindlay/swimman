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

        $this->message = htmlspecialchars($message);
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

        $jsonPayload = array('text' =>
            $this->message);

        $message = "payload=" . json_encode($jsonPayload);

        $c = curl_init($GLOBALS['slackWebhook']);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $message);
        $result = curl_exec($c);

        // If a channel has been set for this notification, also send to that channel
        if (!empty($this->channel)) {

            $jsonPayload['channel'] = $this->channel;

            $message = "payload=" . json_encode($jsonPayload);

            $c = curl_init($GLOBALS['slackWebhook']);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($c, CURLOPT_POST, true);
            curl_setopt($c, CURLOPT_POSTFIELDS, $message);
            $result = curl_exec($c);

        }

        if (curl_error($c)) {

            addlog("Slack Notification", "Unable to send notification", "Error: " . curl_error($c) . " Result = " . $result . " Message: " . $this->message);

        } else {

            addlog("Slack Notification", "Sent notification", "Message: " . $this->message);

        }

        curl_close($c);

    }

}