<?php

namespace Tests\LockDev;

use Tests\TestCase;
use Voronoi\LockDev\Middleware;
use Illuminate\Http\Request;

class MiddlewareTest extends TestCase
{

    private function setEnv($key, $value)
    {
        $_ENV[$key] = $value;
    }

    private function execute()
	{
		$request = new Request;
        $middleware = new Middleware;
        return $middleware->handle($request, function () { return response()->json('', 200); });
	}

    private function setCredentials($username, $password) {
        $this->setEnv('DEV_USERNAME', $username);
        $this->setEnv('DEV_PASSWORD', $password);
        
        if ($username == null) { unset($_ENV['DEV_USERNAME']); }
        if ($password == null) { unset($_ENV['DEV_PASSWORD']); }
    }

    private function attemptLoginUsing($username, $password) {
        $_SERVER['PHP_AUTH_USER'] = $username;
        $_SERVER['PHP_AUTH_PW']   = $password;

        if ($username == null) { unset($_SERVER['PHP_AUTH_USER']); }
        if ($password == null) { unset($_SERVER['PHP_AUTH_PW']); }
    }

    function setUp(): void {
        parent::setUp();
        $this->setCredentials('admin', 'password');
        $this->attemptLoginUsing(null, null);
    }

	public function testAuthorizedForSkippedEnvironments()
	{
        foreach (Middleware::$skipEnvironments as $env) {
            $this->setEnv('APP_ENV', $env);

            $response = $this->execute();

     		$this->assertEquals($response->getStatusCode(), 200);
        }
	}

	public function testUnauthorizedAllNullCredentials()
	{
        $this->setEnv('APP_ENV', 'dev');
        $this->setCredentials(null, null);

        $response = $this->execute();

 		$this->assertEquals($response->getStatusCode(), 401);
	}

	public function testUnauthorizedNoUsernameOrPassword()
	{
        $this->setEnv('APP_ENV', 'dev');

        $response = $this->execute();

 		$this->assertEquals($response->getStatusCode(), 401);
	}

    public function testUnauthorizedNoEnvUsernameOrPassword()
	{
        $this->setEnv('APP_ENV', 'dev');
        $this->setCredentials(null, null);
        $this->attemptLoginUsing('admin', 'password');

        $response = $this->execute();

 		$this->assertEquals($response->getStatusCode(), 401);
	}

    public function testUnauthorizedIncorrectUsername()
	{
        $this->setEnv('APP_ENV', 'dev');
        $this->attemptLoginUsing('admin2', 'password');

        $response = $this->execute();

 		$this->assertEquals($response->getStatusCode(), 401);
	}

    public function testUnauthorizedForIncorrectPassword()
	{
        $this->setEnv('APP_ENV', 'dev');
        $this->attemptLoginUsing('admin', 'password2');

        $response = $this->execute();

 		$this->assertEquals($response->getStatusCode(), 401);
	}

    public function testAuthorizedForCorrectCredentials()
	{
        $this->setEnv('APP_ENV', 'dev');
        $this->attemptLoginUsing('admin', 'password');

        $response = $this->execute();

 		$this->assertEquals($response->getStatusCode(), 200);
	}
}
