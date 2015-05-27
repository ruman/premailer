#PreMailer

##Introduction
Crossjoin\PreMailer converts CSS in a given HTML source to inline styles and optimizes it for sending it via e-mail. It also creates a text version of the HTML source.

##Installation
This is a composer package. See the [composer website](https://getcomposer.org/) for basic installation information.

Add the following line to your `composer.json` file:
```javascript
{
    "require": {
        "crossjoin/pre-mailer": "1.0.*-dev"
    }
}
```

##Features
- Extracts CSS from HTML sources
- Can move CSS to the body of the HTML document (so it won't be removed by some e-mail clients)
- Can remove comments from the HTML document
- Can remove all class attributes from the HTML document
- Compresses the CSS
- Creates a text version of the HTML document (for the alternative part of the e-mail)
- ...

##Usage

###Reading HTML
You can read different HTML sources.

```php
// Read HTML file
$htmlFileName = "path/to/file.html";
$preMailer = new \Crossjoin\PreMailer\HtmlFile($htmlFileName);

// Read HTML string
$htmlString = "<html>...</html>";
$preMailer = new \Crossjoin\PreMailer\HtmlString($htmlString);
```

###Set charset
The default charset is "UTF-8". You can change it to the preferred charset.

```php
// Sets the charset of the HTML file.
$preMailer->setCharset("ISO-8859-1");
```

###Set options
You can set different options to influence the PreMailer behavior.

```php

// Remove HTML comments (default)
$preMailer->setOption($preMailer::OPTION_HTML_COMMENTS, $preMailer::OPTION_HTML_COMMENTS_REMOVE);

// Keep HTML comments
$preMailer->setOption($preMailer::OPTION_HTML_COMMENTS, $preMailer::OPTION_HTML_COMMENTS_KEEP);

// Move the style tag to the body of the HTML document (default)
$preMailer->setOption($preMailer::OPTION_STYLE_TAG, $preMailer::OPTION_STYLE_TAG_BODY);

// Move the style tag to the head of the HTML document
$preMailer->setOption($preMailer::OPTION_STYLE_TAG, $preMailer::OPTION_STYLE_TAG_HEAD);

// Remove the style tag from the HTML document
// (to use, if ALL of your styles can be written inline)
$preMailer->setOption($preMailer::OPTION_STYLE_TAG, $preMailer::OPTION_STYLE_TAG_REMOVE);

// Keep HTML class attributes (default)
$preMailer->setOption($preMailer::OPTION_HTML_CLASSES, $preMailer::OPTION_HTML_CLASSES_KEEP);

// Remove HTML class attributes
$preMailer->setOption($preMailer::OPTION_HTML_CLASSES, $preMailer::OPTION_HTML_CLASSES_REMOVE);

// Set line-width of the text version (defaults to 75)
$preMailer->setOption($preMailer::OPTION_TEXT_LINE_WIDTH, 60);
```

###Generate the content
The PreMailer generated an optimized HTML and text version for the e-mail.

```php
// Get the HTML version
$html = $preMailer->getHtml();

// Get the text version
$text = $preMailer->getText();
```

##To Do
- Add charset auto-detection (extracted from the HTML document)
- Ability to influence the text version format
