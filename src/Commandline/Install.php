<?php
namespace Tualo\Office\MicrosoftMail\Commandline;
use Tualo\Office\Basic\ICommandline;
use Tualo\Office\Basic\CommandLineInstallSQL;

class Install extends CommandLineInstallSQL  implements ICommandline{
    public static function getDir():string {   return dirname(__DIR__,1); }
    public static $shortName  = 'msgraph';
    public static $files = [
        'msgraph_environments' => 'setup msgraph_environments ',
    ];
    
}