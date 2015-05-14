<?php
require_once('lib/nusoap/nusoap.php');
$c = new soapclient('http://104.131.83.197/instantwin.php');
$result = $c->call('getInstantWinners',
array('date' => '2014-11-03'),
"http://104.131.83.197/instantwin.php?wsdl",
"http://104.131.83.197/instantwin.php?wsdl#getInstantWinners");
        // Check for a fault
        if ($c->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            // Check for errors
            $err = $c->soapclient->getError();
            if ($err) {
                // Display the error
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                // Display the result
                echo '<h2>Result</h2><pre>';
                print_r($result);
            echo '</pre>';
            }
        }

print_r($result);
?>