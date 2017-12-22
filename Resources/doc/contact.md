
## Contact

### Configuration

```yaml
# /app/config/config.yml

es_base:
    # ...
    contact: ~
```

All messages are persisted to the database but if you wish to receive contact messages by email,
you can add `deliver_to` option:

```yaml
    contact:
        deliver_to: contact@acmedemo.com
```

[Return to index](index.md)