<?php namespace Voronoi\LockDev;

use Closure;

class Middleware
{

    // `App_ENV` values to skip authentication check
    public static $skipEnvironments = ['local', 'testing', 'production'];

    protected $except = ['\/apprentice\/*'];

    /**
     * Handle the middleware request
     */
    public function handle($request, Closure $next)
    {
        foreach ($this->except as $rule) {
            if (preg_match("/^($rule)/i", $request->getRequestUri())) {
                return $next($request);
            }
        }

        // Skip dev login check
        $env = env('APP_ENV');
        if (in_array($env, Middleware::$skipEnvironments)) {
            return $next($request);
        }

        if (!$this->checkHTTPAuthentication()) {
            return $this->requestPassword();
        } else {
            return $next($request);
        }
    }

    /**
     * Check if HTTP Authentication is valid
     * @return Bool True if http authentication succeeds, false if authentication is invalid (e.g. incorrect or not provided)
     */
    private function checkHTTPAuthentication()
    {
        $user           = $_SERVER['PHP_AUTH_USER'] ?? null;
        $password       = $_SERVER['PHP_AUTH_PW'] ?? null;
        $credentialsSet = !is_null($user) && !is_null($password);
        return $credentialsSet && env('DEV_USERNAME') === $user && env('DEV_PASSWORD') === $password;
    }

    /**
     * Sends HTTP Auth headers
     * @return Response
     */
    private function requestPassword()
    {
        header('WWW-Authenticate: Basic realm="Dev Credentials"');
        header('HTTP/1.1 401 Unauthorized');
        return response('Authorization Required.', 401);
    }
}
