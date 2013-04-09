<?php
function batchAPI ( $calls, $token ) {
    $params = array ( 'batch' => json_encode ( $calls ) );
    $params['access_token'] = $token;
    $url = 'https://graph.facebook.com/';
    $ch = curl_init ( $url );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec ( $ch );
    curl_close ( $ch );
    return $data;
}

function getAPI ( $endpoint, $token ) {
    $tokenString = ( preg_match ( '/\?/', $endpoint ) ? '&' : '?' ) . 'access_token=' . $token;
    $url = 'https://graph.facebook.com/' . $endpoint . $tokenString;
    $ch = curl_init ( $url );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec ( $ch );
    curl_close ( $ch );
    return $data;
}

$calls = array (
    array (
        'method' => 'GET',
        'relative_url' => 'me/friends'
    )
);

$access_token = 'BAACEdEose0cBAGVSknwpHjyhgBK670IyKhOA8QifRBsd4JO0hBOJqUtNtGgS6wO2O7ufn7CuZCuu64p7R9DYIHDpp7H5U3JJf1R4Eq1iyrVBMHOTZC';

$data = json_decode ( batchAPI ( $calls, $access_token ) );

$callses = array ();
$calls = array ();

$output = json_decode ( $data[0]->body );

$n = 0;

foreach ( $output->data as $friend ) {
    $call = array ();
    $call['method'] = 'GET';
    $call['relative_url'] = $friend->id . '/friends';
    array_push ( $calls, $call );
    if ( ++$n >= 50 ) {
        array_push ( $callses, $calls );
        $n = 0;
        $calls = array ();
    }
}

$s = 0;
$f = 0;

foreach ( $callses as $calls ) {
    $data = json_decode ( batchAPI ( $calls, $access_token ) );
    foreach ( $data as $dat ) {
        if ( ! empty ( $dat ) ) {
            if ( $dat->code == 200 ) {
                $s++;
            } else {
                $f++;
            }
        } else {
            $f++;
        }
    }
}

echo "Success: " . $s . "\n";
echo "Failed: " . $f . "\n";
?>
