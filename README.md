This package is a dead simple way to lock access to a dev website behind basic HTTP authentication. This middleware will __not__ authenticate for `local`, `testing`, or `production` environments.


# Installation

1. `composer require voronoi/lock-dev-middleware`

2. Set `DEV_USERNAME` and `DEV_PASSWORD` in your `.env` file.

3. Add `\Voronoi\LockDev\Middleware::class` to your `app/Http/Kernel.php` to lock everything down.

```
protected $middleware = [
	\App\Http\Middleware\DevLogin::class,
	...
];

```

4. Enjoy!


# Tests

Be sure to run the tests with `--stderr`. e.g. `vendor/bin/phpunit --stderr`.
