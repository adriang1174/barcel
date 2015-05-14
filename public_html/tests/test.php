<?php

//$f3=require(__DIR__.'/lib/base.php'); // path to f3 
$f3=require('../lib/base.php'); // path to f3

// Set up
$test=new Test;
include('../app/barcel.php');
include('../app/controller.php');
$b = new Barcel;

// This is where the tests begin
$test->expect(
    is_callable('is_used'),
    'is_used() is a method'
);

// Another test
$res=is_used('12345678');
$test->expect(
    !res,
    'Code 12345678 is not used'
);

// This test should succeed
$res=is_used('20000012');
$test->expect(
    !res,
    'Code 20000012 is used'
);
/*20000012
$test->expect(
    is_string($hello),
    'Return value is a string'
);
*/
// This test is bound to fail
//$test->expect(
//   strlen($hello)==13,
//    'String length is 13'
//);

// Display the results; not MVC but let's keep it simple
foreach ($test->results() as $result) {
    echo $result['text'].'<br>';
    if ($result['status'])
        echo 'Pass';
    else
        echo 'Fail ('.$result['source'].')';
    echo '<br>';
}

?>
