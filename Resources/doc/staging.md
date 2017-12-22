# Staging

## Configuration

The available `authtype`s are `basic` for Basic Authentication and `form` for a form auth.

``` yaml
es_cameleon:
    staging:
        authtype: form
        password: xxx
```

You can either set a single password or define a set of users as below:

``` yaml
es_base:
    staging:
        authtype: form
        users:
            ryan:  { password: ryanpass }
            admin: { password: kitten }
```

In the `security.yml`, define the encoding algorithm for the `Symfony\Component\Security\Core\User\User` class:

```yaml
# security.yml

security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext
```

Import the routing configuration:

``` yaml
# app/config/routing.yml

es_base_staging:
    resource: "@ESBaseBundle/Resources/config/routing/staging.yml"
```

You're done!

[Return to index](index.md)
