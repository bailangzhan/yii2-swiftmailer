<?php

namespace bailangzhan;

use Yii;
use yii\base\InvalidConfigException;

/**
 * 发送邮件处理类
 *
 * Forexample:
 *
 * send a text message
 * 
 * ```php
 *  $message = [
        'to' => 'xxx@qq.com',
        'subject' => 'just a test.',
        'content' => 'This just a test.',
    ];
    $mailer = new Mailer(Mailer::TYPE_1, $messages);
    $result = $mailer->sendMessage();
 * ```
 *
 * If the message you want to send is an html template page, you can do so:
 * ```php
 * $message = [
        'to' => 'xxx@qq.com',
        'subject' => 'just a test.',
        'content' => 'This just a test.',
        'view' => Yii::$app->mailer->viewPath,
        'params' => [
            'name' => 'xiaoming',
            'age' => 20
        ]
    ];
 * ```
 * name and age is the corresponding variable inside the view
 * 
 * Send text messages in bulk
 * ```php
 * $messages = [
                    [
                        'to' => 'xxx@qq.com', 
                        'subject' => 'just a test.',
                        'content' => 'This just a test.',
                    ],
                    [
                        'to' => 'xxx@qq.com', 
                        'subject' => 'just a test2.',
                        'content' => 'This just a test2.',
                    ]
                ];
    $mailer = new Mailer(Mailer::TYPE_2, $messages);
    $result = $mailer->sendMessage();
 *  ```
 */

class Mailer
{
    /**
     * @var Yii::$app->mailer
     */
    private $_mailer;

    /**
     * @var mailer config, name-value pairs
     *
     * For example:
     *
     * ```php
     * [
     *     'to' => '',
     *     'subject' => '',
     *     'content' => '',
     *     'view' => null,
     *     'params' => [],
     * ]
     * ```
     */
    public $message = [];
    public $type;

    // one
    const TYPE_1 = 1;
    // multi
    const TYPE_2 = 2;

    public function __construct($type, $message = null)
    {
        // init
        if ($this->_mailer == null) {
            $this->_mailer = Yii::$app->mailer;
        }
        $this->message = $message;
        $this->type = $type;

        // configuration check
        $this->checkType();
        $this->check();
    }
    /**
     * send a text message
     * @param  array   $message  refer to $this->message
     */
    public function text($message = null)
    {
        !$message && $message = $this->message;
        $result = $this->_mailer
                        ->compose(
                                !empty($message['view']) ? $message['view'] : null, 
                                !empty($message['params']) ? (array) $message['params'] : []
                            )
                        ->setTo($message['to'])
                        ->setSubject($message['subject'])
                        ->setTextBody($message['content']);
        return $result;
    }
    /**
     * Send text messages in bulk
     * @return Array $messages
     */
    public function multiText()
    {
        $messages = []; 
        foreach ($this->message as $message) {
            $messages[] = $this->text($message); 
        } 
        return $messages;
    }

    public function sendMessage()
    {
        try {
            $result = null;
            // choose diffrent send type
            switch ($this->type) {
                case self::TYPE_1:
                    $result = $this->text()->send();
                    break;
                case self::TYPE_2:
                    $messages = $this->multiText();
                    $result = $this->_mailer->sendMultiple($messages);
                    break;
                default:
                    break;
            } 
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $logObject = Yii::getLogger();
            $logObject->log($msg, \yii\log\Logger::LEVEL_ERROR);
            $logObject->flush(true);

            throw new \Exception("Error: {$msg}.", 1);
        }
        
        return $result;
    }
    /**
     * Check the configuration is correct
     */
    public function check()
    {
        $tempMessage = $this->message;
        $multi = current($tempMessage);
        $count = 0;
        switch ($this->type) {
            case self::TYPE_1:
                if (!is_array($multi)) {
                    $this->singleCheck($this->message);
                    $count ++;
                }
                break;
            case self::TYPE_2:
                if (is_array($multi)) {
                    foreach ($this->message as $msg) {
                        $this->singleCheck($msg);
                        $count ++;
                    }
                }
                break;
            default:
                break;
        }
        if (!$count) {
            throw new InvalidConfigException("Your configuration is wrong.", 1);
        }
        return $count;
    }
    public function singleCheck($message)
    {
        if (!is_array($message)) {
            throw new InvalidConfigException("Mailer::\$message must be an array, please refer to Mailer::\$message configuration.", 1);
        }
        if (empty($message['to']) || empty($message['subject'])) {
            throw new InvalidConfigException("\$message['to'] and \$message['subject'] must be set.", 1);
        }
        if (empty($message['view']) && empty($message['content'])) {
            throw new InvalidConfigException("If you have not set \$message['view'], you have to set \$message['content'].", 1);
        }
        return true;
    }
    public function checkType()
    {
        if (!in_array($this->type, [self::TYPE_1, self::TYPE_2])) {
            throw new InvalidConfigException("you have not set up Mailer::\$type, please refer to Mailer::\$type configuration.", 1);
        }
    }
}