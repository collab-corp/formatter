<?php

use CollabCorp\Formatter\Formatter;

require __DIR__.'/vendor/autoload.php';



//using this file simply for tmp testing
dd((new Formatter(['123something', ["foo123","bar456",'baz678']]))->onlyNumbers()->divide(2)->roundTo(2));
