<?php
namespace App\Http\Controllers\Api;

  class NotiSend{

static  function sendNotif($token,$title,$msg){

            $from = "AAAAdMPQRUw:APA91bGqNp74BHXYOEMoTB2VcNmae-2X-sNgSIf8YqPFb5Q5UOzKfinCVeiqHxErT53BdjFN0yY_vHLHE34z_m11pNZYAt_p0kpmvaLwexkA0pj4FWxWaE9yXWysvyERF6MMjisjgala";
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
