<?php

class MyTest extends Controller {

	function test_is_used2($f3){
		error_log('Test done');
		echo 'Test done';
	}

	function test_is_used($f3) {

		$test=new Test;
		$b = new Barcel;

		$res=$b->is_used('12345678');
		$test->expect(
			!$res,
			'Code 12345678 is not used'
		);

		$res=$b->is_used('20000012');
		$test->expect(
			$res==true,
			'Code 20000012 is used'
		);
		 $res=$b->is_dup_week();
		 $test->expect(
                        $res==true,
                        'Is dup week!!!'
                );

		foreach ($test->results() as $result) {
			echo $result['text'].'<br>';
			if ($result['status'])
				echo 'Pass';
			else
				echo 'Fail ('.$result['source'].')';
			echo '<br>';
		}

	}

        function test_instant_win($f3) {

                $test=new Test;
                $b = new Barcel;

                $res=$b->is_instant_win('9E88F25E','eb3a3cb29c3f4ced8e71c47e5b1913fb');
                $test->expect(
                        $res=='TRUE',
                        'Instant win'
                );
                $res=$b->is_instant_win('3CDA9AF1','297db82a99d14c4db17d80afea506617');
                $test->expect(
                        $res=='FALSE',
                        'Not instant win'
                );
                $res=$b->is_instant_win('223A5D37','297db82a99d14c4db17d80afea506617');
                $test->expect(
                        $res=='FALSE',
                        'Not instant win'
                );
               $res=$b->is_instant_win('3C4EB159','297db82a99d14c4db17d80afea506617');
                $test->expect(
                        $res=='FALSE',
                        'Not instant win'
                );
                $res=$b->is_instant_win('8R679E46','_guid_AOklf8qLy1CBmbQiYo3m03nyTI1tqAQuV_mWtuZ5ALc=');
                $test->expect(
                        $res=='FALSE',
                        'Not Instant win'
                );


                foreach ($test->results() as $result) {
                        echo $result['text'].'<br>';
                        if ($result['status'])
                                echo 'Pass';
                        else
                                echo 'Fail ('.$result['source'].')';
                        echo '<br>';
                }

        }

}
?>
