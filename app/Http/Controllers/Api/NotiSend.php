<?php
namespace App\Http\Controllers\Api;

  class NotiSend{

static  function sendNotif($token,$title,$msg){

            $from = "fhN_R73bEm60PmYfOtVoWY:APA91bEYCCW_yDgW_tJb_5R59BHeXiSUM5kz-Is5hUae9vLBtSUTTahsQpmsG8qAHkdbqhSmcvxJ14XwGEfvhA1A7xVJQLUqSNSo6-0kbmrTBlEaVRK1IMo_wpc8NiXA6gEK-UqldARA";
            $msg = array
              (
                'body'  => "$msg",
                'title' => "$title",
                'receiver' => ' ',
                'icon'  => "https://image.flaticon.com/icons/png/512/270/270014.png",/*Default Icon*/
                'sound' => 'mySound'/*Default sound*/
              );

        $fields = array
                (
                    'to'        => $token,
                    'notification'  => $msg
                );

        $headers = array
                (
                    'Authorization: key=' . $from,
                    'Content-Type: application/json'
                );
        //#Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );


    }
}

?>
