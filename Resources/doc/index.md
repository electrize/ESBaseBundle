
# Base Bundle

- [Assetic with sprites](assetic.md)
- [Staging configuration](staging.md)
- [Google Analytics](google_analytics.md)
- [Mailer](mailer.md)
- [Contact](contact.md)
- [Feedback](feedback.md) (UserVoice)
- [Load more](loadmore.md)

## Installation

### Configure bootstrap bundle

Enable BraincraftedBootstrapBundle:

```php
# app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Braincrafted\Bundle\BootstrapBundle\BraincraftedBootstrapBundle(),
        );
    }
}
```

## TODO

Handle Google Analytics tags shot by other bundles

ex: ESUserBundle registration controller shoot a "register" tag for GA