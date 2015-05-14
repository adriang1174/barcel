<?php
try{

    $opts = array(
        'http'=>array(
            'user_agent' => 'PHPSoapClient'
            )
        );

    $context = stream_context_create($opts);
    $client = new SoapClient('http://104.131.83.197/instantwin.php?wsdl',
                             array('stream_context' => $context,
                                   'cache_wsdl' => WSDL_CACHE_NONE));

    $result = $client->getInstantWinners('2014-11-27');
print_r($result);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
