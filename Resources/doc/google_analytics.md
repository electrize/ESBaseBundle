# Google Analytics

## Configuration

Set up your trackers

> Note: First tracker is the default one

```yaml
    google_analytics:
        website_name:         %cameleon.project_name%
        trackers:
            main: UA-47067754-2
            admin:  UA-47067754-3
        tracked_environments:
            - prod
```

If you need to enable GA in your staging environment, see below:

```yaml
    google_analytics:
        tracked_environments:
            - prod
            - staging
```

`tracked_environments` is used to defined in which cameleon environments the data is sent to Google.

[Return to index](index.md)
