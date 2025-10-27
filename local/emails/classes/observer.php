<?php
namespace local_emails;

class observer {
    public static function on_email_sent(\local_emails\event\email_sent $event) {
        debugging('Se ha detectado el evento email_sent para el usuario '.$event->userid);
    }
}
