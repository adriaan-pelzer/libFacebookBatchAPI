<?php
class FacebookBatchAPI {
    private $calls;
    private $results;
    private $token;
    private $rawdata;
    private $data;

    public function __construct ( $token ) {
        $this->token = $token;
        $this->calls = array ();
    }

    function addCall ( $method, $relative_url ) {
        $call = array ();
        $call['method'] = $method;
        $call['relative_url'] = $relative_url;
        array_push ( $calls, $call );
    }

    function flushCalls () {
        $params = array ( 'batch' => json_encode ( $this->calls ) );
        $params['access_token'] = $this->token;
        $url = 'https://graph.facebook.com/';
        $ch = curl_init ( $url ) || throw new Exception ( 'Cannot initialise curl' );
        curl_setopt ( $ch, CURLOPT_POST, 1 ) || throw new Exception ( 'Cannot set curl option CURLOPT_POST' );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params ) || throw new Exception ( 'Cannot set curl option CURLOPT_POSTFIELDS' );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ) || throw new Exception ( 'Cannot set curl option CURLOPT_RETURNTRANSFER' );
        $this->rawdata = curl_exec ( $ch ) || throw new Exception ( 'Cannot execute curl connection' );
        curl_close ( $ch );
        $this->data = json_decode ( $this->data ) || throw new Exception ( 'Cannot parse output json' );
        $this->calls = array ();
        return $this->data;
    }

}

$calls = array (
    array (
        'method' => 'GET',
        'relative_url' => 'me/friends'
    )
);

$access_token = 'BAACEdEose0cBAGVSknwpHjyhgBK670IyKhOA8QifRBsd4JO0hBOJqUtNtGgS6wO2O7ufn7CuZCuu64p7R9DYIHDpp7H5U3JJf1R4Eq1iyrVBMHOTZC';

$BatchAPI = new FacebookBatchAPI ( $access_token );

$BatchAPI->addCall ( 'GET', 'me/friends' );

print_r ( $BatchAPI->flushCalls () );

/*$data = json_decode ( batchAPI ( $calls, $access_token ) );

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
echo "Failed: " . $f . "\n";*/
?>
