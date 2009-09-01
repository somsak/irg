<?php
class Utils{
    public static function convertToTimestamp($dateTime){
        # 2000/08/25 23:30
        $val = explode(" ",$dateTime);
        $date = explode("/",$val[0]);
        $time = explode(":",$val[1]);

        return substr(mktime($time[0], $time[1], 0, $date[1], $date[2], $date[0]), 0, 10);
    }
}
?>
