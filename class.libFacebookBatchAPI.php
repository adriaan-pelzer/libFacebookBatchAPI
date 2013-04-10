<?php
class FacebookBatchAPI {
    private $calls;
    private $results;
    private $token;
    private $rawdata;
    private $data;
    private $debug = TRUE;

    private function _log ( $message ) {
        if ( $this->debug ) {
            echo $message . "\n";
        }
    }

    public function __construct ( $token ) {
        $this->token = $token;
        $this->calls = array ();
        $this->results = array ();
    }

    public function addCall ( $method, $relative_url ) {
        $call = array ();
        $call['method'] = $method;
        $call['relative_url'] = $relative_url;
        array_push ( $this->calls, $call );
    }

    private function flushOnce () {
        $this->_log ( "Calling flushOnce with " . sizeof ( $this->calls ) . " calls" );

        $params = array ( 'batch' => json_encode ( $this->calls ) );
        $params['access_token'] = $this->token;
        $url = 'https://graph.facebook.com/';

        if ( ! ( $ch = curl_init ( $url ) ) ) {
            throw new Exception ( 'Cannot initialise curl' );
        }
        if ( ! ( curl_setopt ( $ch, CURLOPT_POST, 1 ) ) ) {
            throw new Exception ( 'Cannot set curl option CURLOPT_POST' );
        }
        if ( ! ( curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params ) ) ) {
            throw new Exception ( 'Cannot set curl option CURLOPT_POSTFIELDS' );
        }
        if ( ! ( curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ) ) ) {
            throw new Exception ( 'Cannot set curl option CURLOPT_RETURNTRANSFER' );
        }
        if ( ! ( $this->rawdata = curl_exec ( $ch ) ) ) {
            throw new Exception ( 'Cannot execute curl connection' );
        }

        curl_close ( $ch );

        if ( ( $this->data = json_decode ( $this->rawdata ) ) == NULL ) {
            throw new Exception ( 'Cannot parse output json' );
        }

        $callCache = $this->calls;
        $this->calls = array ();

        return $callCache;
    }

    public function flushCalls () {
        $this->_log ( "Calling flushCalls with " . sizeof ( $this->calls ) . " calls" );

        while ( sizeof ( $this->calls ) ) {
            $callCache = $this->flushOnce ();

            for ( $i = 0; $i < sizeof ( $this->data ); $i++ ) {
                if ( $this->data[$i] == NULL ) {
                    array_push ( $this->calls, $callCache[$i] );
                } else {
                    $result = array ();
                    $result['call'] = $callCache[$i];

                    if ( $this->data[$i]->code == 200 ) {
                        $this->data[$i]->body = json_decode ( $this->data[$i]->body );
                    }

                    $result['result'] = $this->data[$i];
                    array_push ( $this->results, $result );
                }
            }
        }

        return $this->results;
    }

}

$access_token = 'BAACEdEose0cBAEKbrRp9SE9aImt3fIg9LQTpeLN0RBAfEr70gLOfZAhqDCu5KOwtt1yaIs1nyOSLHDZCk02hmzHLEp29nbihmLcLOckBZCZBHYJtiRV1HUUpo9khoB7uKDNXL9IOsmdeqVfsVbfVnr1CIfTRz8CPenCwDmxtHaLNAxKlqgAj8DPDs2ZCtr5keZBvCnQWAvvgZDZD';

$BatchAPI = new FacebookBatchAPI ( $access_token );

$BatchAPI->addCall ( 'GET', 'me/friends' );

$results = $BatchAPI->flushCalls ();

$n = 0;
$s = 0;
$f = 0;

foreach ( $results[0]['result']->body->data as $friend ) {
    $BatchAPI->addCall ( 'GET', $friend->id . '/friends' );

    if ( ++$n >= 50 ) {
        $results = $BatchAPI->flushCalls ();

        foreach ( $results as $result ) {
            if ( $result['result']->code == 500 ) {
                $f++;
            } else {
                $s++;
            }
        }

        $n = 0;
    }
}

echo "Success: " . $s . "\n";
echo "Failed: " . $f . "\n";

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
