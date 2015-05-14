<?php
$f3 = require('f3/lib/base.php');
$f3->route('GET /',
    function() {
        echo 'Hello, world!';
    }
);
$f3->route('GET /brew/@count',
    function($f3) {
        echo $f3->get('PARAMS.count').' bottles of beer on the wall.';
    }
);
$f3->run();
?>
