<?php

namespace codesaur\Http\Client\Example;

/* DEV: v1.2021.10.09
 * 
 * This is an example script!
 */

require_once '../vendor/autoload.php';

use codesaur\Http\Client\Client;

$client = new Client();
echo $client->request('www.google.com/');
