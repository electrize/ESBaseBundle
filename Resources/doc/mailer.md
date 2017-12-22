
## Mailer:

### Configuration

Verify your `swiftmailer` configuration:

```yaml
swiftmailer:
    transport: "%mailer_transport%"
    host:      "%mailer_host%"
    port:      "%mailer_port%"
    username:  "%mailer_user%"
    password:  "%mailer_password%"
    spool:     { type: memory }
```

Enable the mail and set the required `sender_address` parameter.

```yaml
# /app/config/config.yml
es_base:
    # ...
    mailer:
        sender_address: no-reply@acmedemo.com
```

### Usage

#### Mail template

All parts of the mail are in one template.
In your mail template, the following blocks must be present:
- `subject` Define the subject of the mail (plain text)
- `body_text` The text/plain part of the mail
- `body_html` The text/html part of the mail

This bundle provides a mail layout (`ESBaseBundle:Mail:base.html.twig`) implementing these blocks for you.
You just have to fill the custom and the signature will be appended.

You can use this template and write your mail this way:

```django
{# @AcmeDemoBundle/Resources/views/Mail/my_custom_mail.html.twig #}
{% extends 'ESBaseBundle:Mail:base.html.twig' %}

{% block subject %}
	Welcome!
{% endblock %}

{% block content_text %}
	Hello,

	How are you?
	 \o/
	  |
	 /\

	See you!
{% endblock %}

{% block content_html %}
	<p>Hello,</p>

	<p>How are you?</p>
	<img src="http://path/to/img">
	<hr>
	<p>See you!</p>
{% endblock %}
```

#### Send emails

On the backend side you can use the `es_base.mailer` service:

```php
$mailer = $this->container->get('es_base.mailer');
$mailer->send('AcmeDemoBundle:Mail:my_custom_mail.html.twig',
	'recipient@website.com',
	array('message' => $message)
);
```

An optional 4th argument is used to override the sender email.

You you intend to send a mail to the authenticated user, you should use:

```php
$mailer->sendToUser(
	'AcmeDemoBundle:Mail:my_custom_mail.html.twig',
	array('message' => $message),
	$user
);
```

#### Attachments

You can pass some files path to the mailer service to attach these ones to the mail:

```php
$mailer->send('AcmeDemoBundle:Mail:my_custom_mail.html.twig',
	'recipient@website.com',
	array('message' => $message),
	null, // sender email, by default its the one set in your configuration
	['/var/www/uploads/a.jpg', '/var/www/static/demo.pdf']
);
```

#### Command line

In order to test your mail configuration you can send test mails with the command `es:mailer:send`:

```bash
$ bin/console es:mailer:send ademoulins@entropic-synergies.com 'ESBaseBundle:Mail:test.html.twig' -a /path/to/attached-file.pdf
```

[Return to index](index.md)
