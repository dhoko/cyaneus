<?php 
require_once __DIR__.'/core/autoload.php';
require_once __DIR__.'/core/Factory.php';

use Symfony\Component\Console as Console;

$application = new Console\Application('cyaneus', '1.0.0');
$application->add(new Commands\CreateCommand('create'));
$application->run();