# Stylers Taxonomy Module

## Notes

### Language codes
https://en.wikipedia.org/wiki/ISO_639-1
https://www.w3.org/International/articles/language-tags/

## Dependencies
- https://github.com/etrepat/baum
 - composer.json - `"require": {
        "baum/baum": "~1.1"
    }`
 - As with most Laravel 5 packages you'll then need to register the Baum service provider. To do that, head over your config/app.php file and add the following line into the providers array: 'Baum\Providers\BaumServiceProvider'
