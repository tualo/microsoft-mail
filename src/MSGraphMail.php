<?php

namespace Tualo\Office\MicrosoftMail;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MicrosoftMail\GraphHelper;
use Microsoft\Graph\Generated\Models\User;
use Tualo\Office\MicrosoftMail\API;



class MSGraphMail {
    public static function get(): MSGraphMail{
        GraphHelper::initializeGraphForUserAuth();
        $config = json_decode(API::env('primary') ,true);
        GraphHelper::setAccessToken( $config['access_token'] );
        return new MSGraphMail();
    }


    public function addBCC(string $email, string $name=""){
        return true;
    }

    public function setFrom(string $email, string $name=""){
        return true;
    }

    public function addAddress(string $email, string $name=""){
        $this->recipients[] = ['email'=>$email,'name'=>$name];
        return true;
    }

    public function addAttachment(string $path, string $name=""){
        $this->attachments[] = [
            'path'=>$path,'name'=>$name,'content'=>file_get_contents($path),'contentType'=>mime_content_type($path)];
        return true;
    }

    public function addReplyTo(string $email, string $name=""){
        return true;
    }

    public function isHtml($isHtml){
        $this->isHtml = $isHtml;
        return true;
    }
    public bool $isHtml=false;

    public string $Subject='';
    public string $Body='';
    public string $AltBody='';
    public string $ErrorInfo='';



    private array $recipients=[];
    private array $attachments=[];
    

    public function send(){
        $alt = '';
        $html = '';
        if ($this->isHtml){
            $alt = strip_tags($this->Body);
            $html = $this->Body;
        }else{
            $alt = $this->Body;
            $html = nl2br($this->Body);
        }
        for($i=0;$i<count($this->recipients);$i++){

            GraphHelper::sendMail(
                $this->Subject, 

                $alt, 
                $html,

                $this->recipients[$i]['email'],
                
                $this->attachments
                

            );
        }
        return true;
    }


        // fake properties
        public string $CharSet = 'utf-8';
        public string $Host = 'smtp.office365.com';
        public string $SMTPAuth = 'text/plain';
        public string $Username='';
        public string $Password ='';
        public string $SMTPSecure='';
        public string $Port='';
        public string $SMTPAutoTLS='';
        public string $SMTPOptions='';
        // end fake properties
    
}