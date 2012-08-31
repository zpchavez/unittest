<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class for testing controllers.
 */
abstract class Kohana_Unittest_Controller_TestCase extends Unittest_TestCase
{
	/**
	 * @var Response
	 */
	protected $_response;

	/**
	 * If response was a redirect, will be an array with keys 'url' and 'code'.
	 *
	 * @var array
	 */
	protected $_redirect;

	public function setUp()
	{
		Kohana_Test::prepend_path(MODPATH.'unittest/standins/controller/');
		parent::setUp();
	}

	public function tearDown()
	{
		Auth::reset_instance_returned();
		ORM::reset_factory_output();
		Kohana_Test::reset_paths();
		$this->resetResponse();
		parent::tearDown();
	}

	/**
	 * Discard the response from the last request.
	 */
	public function resetResponse()
	{
		$this->_response = NULL;
		$this->_redirect = NULL;
	}

	/**
	 * Simulate a logged in user.
	 *
	 * @param  Model_Auth_User $user  The object returned by Auth::get_user().
	 */
	public function withUserLoggedIn($user)
	{
		$authMock = $this->getMockBuilder('Auth_ORM')
			->disableOriginalConstructor()
			->getMock();

		$authMock->expects($this->any())
			->method('logged_in')
			->will($this->returnValue(TRUE));

		$authMock->expects($this->any())
			->method('get_user')
			->will($this->returnValue($user));

		Auth::set_instance_returned($authMock);
	}

	/**
	 * Make a get request and save the response in $this->_response.
	 *
	 * @param  string $routeName
	 * @param  array  $routeParams
	 * @param  array  $getParams
	 * @return Kohana_Unittest_Controller_TestCase  This object.
	 */
	public function makeGetRequest($routeName, $routeParams = array(), $getParams = array())
	{
		$uri = Route::url($routeName, $routeParams);

		try
		{
			$request = Request::factory($uri);
			$this->_response = $request
				->query($getParams)
				->execute();
		}
		catch (RedirectException $e)
		{
			$this->_redirect = json_decode($e->getMessage(), TRUE);
		}
	}

	/**
	 * Make a post request and save the response in $this->_response.
	 *
	 * @param  string $routeName
	 * @param  array  $routeParams
	 * @param  array  $getParams
	 * @return Kohana_Unittest_Controller_TestCase  This object.
	 */
	public function makePostRequest($routeName, $routeParams = array(), $postParams = array())
	{
		$uri = Route::url($routeName, $routeParams);

		try
		{
			$request = Request::factory($uri);
			$this->_response = $request
				->method(Http_Request::POST)
				->post($postParams)
				->execute();
		}
		catch (RedirectException $e)
		{
			$this->_redirect = json_decode($e->getMessage(), TRUE);
		}
	}

	/**
	 * Make a post request and save the response in $this->_response.
	 *
	 * @param  string $routeName
	 * @param  array  $routeParams
	 * @param  array  $getParams
	 * @return Kohana_Unittest_Controller_TestCase  This object.
	 */
	public function makePutRequest($routeName, $routeParams = array(), $putParams = array())
	{
		$uri = Route::url($routeName, $routeParams);

		try
		{
			$request = Request::factory($uri);
			$this->_response = $request
				->method(Http_Request::PUT)
				->post($putParams)
				->execute();
		}
		catch (RedirectException $e)
		{
			$this->_redirect = json_decode($e->getMessage(), TRUE);
		}
	}

	public function makeDeleteRequest($routeName, $routeParams = array())
	{
		$uri = Route::url($routeName, $routeParams);

		try
		{
			$request = Request::factory($uri);
			$this->_response = $request
				->method(Http_Request::DELETE)
				->execute();
		}
		catch (RedirectException $e)
		{
			$this->_redirect = json_decode($e->getMessage(), TRUE);
		}
	}

	/**
	 * Assert than an HTML (or XML) string has a match for a CSS selector and,
	 * optionally, that it contains the specified pattern in its content.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 * @param  string $html
	 * @param  string $selector CSS selector.
	 * @param  string $pattern  A regex pattern to match the element's content.
	 */
	public function assertHtmlContains($html, $selector, $pattern = NULL)
	{
		if ($pattern)
		{
			$this->assertSelectRegExp(
				$selector,
				$pattern,
				array('>=' => 1),
				$html,
				'Failed asserting that response body contains selector "'.$selector
				.'" containing the pattern "'.$pattern.'".'
			);
		}
		else
		{
			$this->assertSelectCount(
				$selector,
				array('>=' => 1),
				$html,
				'Failed asserting that response contains selector "'.$selector.'".'
			);
		}
	}

	/**
	 * Assert that the response body has a match for a CSS selector and,
	 * optionally, that it contains the specified pattern in its content.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 * @param  string $selector CSS selector.
	 * @param  string $pattern  A regex pattern to match the element's content.
	 */
	public function assertResponseContains($selector, $pattern = NULL)
	{
		$response = $this->_response;
		if (!$response) {
			$this->fail('No response found.');
		}

		$this->assertHtmlContains($response->body(), $selector, $pattern);
	}

	/**
	 * Assert that a redirect occurred.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public function assertRedirect()
	{
		$this->assertNotNull($this->_redirect, 'Failed asserting that a redirect occurred.');
	}

	/**
	 * Assert that a redirect did not occur.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public function assertNotRedirect()
	{
		$this->assertNull($this->_redirect, 'Failed asserting that no redirect occurred.');
	}

	/**
	 * Assert that a redirect to a particular destination occurred.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 * @param  string $routeName
	 * @param  array  $routeParams
	 */
	public function assertRedirectTo($routeName, $routeParams = array())
	{
		$this->assertRedirect();

		$expected = Route::get($routeName)->uri($routeParams);

		$this->assertEquals(
			$expected,
			Arr::get($this->_redirect, 'url'),
			'Failed asserting redirect to '.$expected
		);
	}

	/**
	 * Assert the value of the decoded JSON response.
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 * @param  mixed $expected
	 * @param  bool  $asObject  Whether to decode as an object instead of an array.
	 */
	public function assertJsonDecodedResponseEquals($expected, $asObject = FALSE)
	{
		$decoded_response = json_decode($this->_response->body(), ! $asObject);
		$this->assertEquals($expected, $decoded_response);
	}
}