<?php
namespace Tualo\Office\MicrosoftMail\Checks;

use Tualo\Office\Basic\Middleware\Session;
use Tualo\Office\Basic\PostCheck;
use Tualo\Office\Basic\TualoApplication as App;


class Tables  extends PostCheck {
    
    public static function test(array $config){
        $clientdb = App::get('clientDB');
        if (is_null($clientdb)) return;
        $tables = [
            'msgraph_environments'=>[
                'columns'=>[
                    'id'=>'varchar(36)'
                ]
            ],

        ];
        self::tableCheck('ds',$tables,
            "please run the following command: `./tm install-sql-msgraph --client ".$clientdb->dbname."`",
            "please run the following command: `./tm install-sql-msgraph --client ".$clientdb->dbname."`"

        );
    }
}