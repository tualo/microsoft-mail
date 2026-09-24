<?php

namespace Tualo\Office\MicrosoftMail;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Microsoft\Graph\Generated\Models\User;
use Tualo\Office\Mail\MailInterface;
use Tualo\Office\MSGraph\API as MSGraphAPI;
use Microsoft\Graph\Generated\Models;
use Microsoft\Graph\Generated\Users\Item\SendMail\SendMailPostRequestBody;
use Microsoft\Graph\Generated\Models\Message;
use Microsoft\Graph\Generated\Models\ItemBody;
use Microsoft\Graph\Generated\Models\BodyType;
use Microsoft\Graph\Generated\Models\Recipient;
use Microsoft\Graph\Generated\Models\EmailAddress;
use Microsoft\Graph\Generated\Models\FileAttachment;
use Microsoft\Kiota\Abstractions\ApiException;
use Microsoft\Graph\Generated\Models\ODataErrors\ODataError;



class MSGraphMail implements MailInterface
{
    public static function get(): MSGraphMail
    {
        MSGraphAPI::GraphClient();
        return new MSGraphMail();
    }


    public function addBCC(string $email, string $name = "")
    {
        return true;
    }

    public function setFrom(string $email, string $name = "")
    {
        return true;
    }

    public function addAddress(string $email, string $name = "")
    {
        $this->recipients[] = ['email' => $email, 'name' => $name];
        return true;
    }

    public function addAttachmentData(string $path, string $content, string $contentType, string $name = "")
    {
        $this->attachments[] = [
            'path' => $path,
            'name' => $name,
            'content' => $content,
            'contentType' => $contentType
        ];
        return true;
    }


    public function addAttachment(string $path, string $name = "")
    {
        $this->attachments[] = [
            'path' => $path,
            'name' => $name,
            'content' => file_get_contents($path),
            'contentType' => mime_content_type($path)
        ];
        return true;
    }

    public function addReplyTo(string $email, string $name = "")
    {
        return true;
    }

    public function isHtml($isHtml)
    {
        $this->_isHtml = $isHtml;
        return true;
    }
    public bool $_isHtml = false;

    public string $Subject = '';
    public string $Body = '';
    public string $AltBody = '';
    public string $ErrorInfo = '';
    public string $listUnsubscribePost = '';



    private array $recipients = [];
    private array $attachments = [];


    public function setSubject(string $value)
    {
        $this->Subject = $value;
        return $this;
    }

    public function setAlternativeBody(string $value)
    {
        $this->AltBody = $value;
        return $this;
    }

    public function setBody(string $value)
    {
        $this->Body = $value;
        return $this;
    }

    public function setListUnsubscribePost(string $value)
    {
        $this->listUnsubscribePost = $value;
        return $this;
    }


    public function send()
    {
        $alt = '';
        $html = '';
        if ($this->_isHtml) {
            $alt = strip_tags($this->Body);
            $html = $this->Body;
        } else {
            $alt = $this->Body;
            $html = nl2br($this->Body);
        }
        for ($i = 0; $i < count($this->recipients); $i++) {

            self::sendMail(
                $this->Subject,

                $alt,
                $html,

                $this->recipients[$i]['email'],

                $this->attachments,
                $this->listUnsubscribePost


            );
        }
        return true;
    }

    private static function sendMail(
        string $subject,
        string $bodyText,
        string $bodyHtml,
        string $recipient,
        array $attachments = [],
        string $listUnsubscribePost = ""
    ): void {
        try {
            $graphClient = MSGraphAPI::GraphClient();
            $requestBody = new SendMailPostRequestBody();
            $message = new Message();
            $message->setSubject($subject);
            $message->setFrom(
                (new Recipient())->setEmailAddress(
                    (new EmailAddress())->setAddress(MSGraphAPI::env('mailFromAddress'))
                )
            );

            if ($bodyText !== '') {
                $messageBody = new ItemBody();
                $messageBody->setContentType(new BodyType('text'));
                $messageBody->setContent($bodyText);
                $message->setBody($messageBody);
            }

            if ($bodyHtml !== '') {
                $messageBody = new ItemBody();
                $messageBody->setContentType(new BodyType('html'));
                $messageBody->setContent($bodyHtml);
                $message->setBody($messageBody);
            }

            $toRecipient = new Recipient();
            $toRecipient->setEmailAddress((new EmailAddress())->setAddress($recipient));
            $message->setToRecipients([$toRecipient]);

            $attachmentsArray = [];
            foreach ($attachments as $attachment) {
                $fileAttachment = new FileAttachment();
                if (isset($attachment['isInline'])) {
                    $fileAttachment->setIsInline($attachment['isInline']);
                }
                $fileAttachment->setName($attachment['name']);
                if (isset($attachment['contentType'])) {
                    $fileAttachment->setContentType($attachment['contentType']);
                }
                if (isset($attachment['content'])) {
                    $fileAttachment->setContentBytes(
                        \GuzzleHttp\Psr7\Utils::streamFor(base64_encode($attachment['content']))
                    );
                }
                $attachmentsArray[] = $fileAttachment;
            }
            if (count($attachmentsArray) > 0) {
                $message->setAttachments($attachmentsArray);
            }

            if ($listUnsubscribePost !== '') {
                $extendedProperty = new Models\SingleValueLegacyExtendedProperty();
                $extendedProperty->setId('String 0x1045');
                $extendedProperty->setValue($listUnsubscribePost);
                $message->setSingleValueExtendedProperties([$extendedProperty]);
            }

            $requestBody->setMessage($message);
            if (MSGraphAPI::has('defaultUserId')) {
                $graphClient->users()->byUserId(MSGraphAPI::env('defaultUserId'))->sendMail()->post($requestBody)->wait();
            } else {
                $graphClient->me()->sendMail()->post($requestBody)->wait();
            }
        } catch (ODataError $exception) {
            throw new \Exception($exception->getError()->getMessage());
        } catch (ApiException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }


    // fake properties
    public string $CharSet = 'utf-8';
    public string $Host = 'smtp.office365.com';
    public string $SMTPAuth = 'text/plain';
    public string $Username = '';
    public string $Password = '';
    public string $SMTPSecure = '';
    public string $Port = '';
    public string $SMTPAutoTLS = '';
    public string $SMTPOptions = '';
    // end fake properties

}
