yii2-swiftmailer
==========================

此扩展是 Yii2 swiftmailer 邮件类的封装，可直接调用并发送邮件

## 安装


推荐使用composer进行安装

```
$ php composer.phar require bailangzhan/yii2-swiftmailer "@dev"
```

或者添加

```
"bailangzhan/yii2-swiftmailer": "@dev"
```

到你的`composer.json`文件的`require`中

## 使用

### 配置你的mailer组件


```
'mailer' => [
    'class' => 'yii\swiftmailer\Mailer',
    'viewPath' => '@common/mail',
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp address',
        'username' => 'your email username',
        'password' => 'password',
        'port' => '25',
        'encryption' => 'tls',
    ],
    'messageConfig'=>[  
       'charset'=>'UTF-8',  
       'from'=>['your email username' => 'your app name']  
    ],
],
```

### 调用Mailer

#### 发送一封邮件

```php
use bailangzhan\Mailer;
$message = [
    'to' => '要发送给谁',
    'subject' => '邮件标题',
    'content' => '邮件内容',
];
$mailer = new Mailer(Mailer::TYPE_1, $messages);
$result = $mailer->sendMessage();
```

#### 发送模版邮件

```php
$message = [
    'to' => '要发送给谁',
    'subject' => '邮件标题',
    'view' => 'mail-template',
    'params' => [
        'name' => '白狼栈',
    ]
];
```

发送模版邮件不需要指定content, 但是需要配置模版名, 即view的值，假如你有一个位于 Yii::$app->mailer->viewPath目录下的 mail-template.php 模版，view 填写 mail-template 就好

#### 批量发送邮件

```php
$messages = [
	[
		'to' => '要发送给谁',
		'subject' => '邮件标题',
		'content' => '邮件内容',
	],
	[
		'to' => '要发送给谁',
		'subject' => '邮件标题',
		'content' => '邮件内容',
		'view' => 'mail-template',
		'params' => [
			'name' => '白狼栈',
		]
	]
];
$mailer = new Mailer(Mailer::TYPE_2, $messages);
$result = $mailer->sendMessage();
```

## 许可

**yii2-swiftmailer** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.
