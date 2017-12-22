# Assetic

## Configuration

In order to generate sprites via compass, you should use the `compass_sprite` filter provided by this bundle.

Set the following configuration:

```yaml
# /app/config/config.yml

assetic:
    filters:
        compass_sprite:
            bin: %compass_bin_path%
            apply_to: "\.scss$"
            resource: '%kernel.root_dir%/../vendor/es/base-bundle/ES/Bundle/BaseBundle/Resources/config/filters/compass_sprite.xml'
```

## Usage

The compass configuration parameters like `images-dir`, `generated_images_path`, `http_images_path` and `http_generated_images_path` will automatically be set for each bundle.

```css
/* src/Demo/AcmeBundle/Resources/css/sass/style.scss */

div.bg {
	background: image-url('bg.jpg'); /* will use /bundles/{bundle}/images/bg.jpg */
	width: image-width('bg.jpg'); /* will check src/Demo/AcmeBundle/Resources/public/images/bg.jpg */
}
```

## Known issues

If you run `assets:install` with the `--symlink` options, generated images (sprites) will be placedinto your Resources directory.
You should run `assets:install` without the symlink option to prevent the sprite images from being added to your git repository.

[Return to index](index.md)
