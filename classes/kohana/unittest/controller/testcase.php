<?php defined('SYSPATH') or die('No direct script access.');

require Kohana::find_file('vendor/Zend/Dom', 'Query');

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
		Kohana_Test::reset_paths();
		$this->reset_response();
		parent::tearDown();
	}

	/**
	 * Discard the response from the last request.
	 */
	public function reset_response()
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
			->will($this->returnValue(true));

		$authMock->expects($this->any())
			->method('get_user')
			->will($this->returnValue($user));

		Auth::set_instance_returned($authMock);
	}

	/**
	 * Make a get request and save the response in $this->response.
	 *
	 * @param  string $routeName
	 * @param  array  $routeParams
	 * @param  array  $getParams
	 * @return Kohana_Unittest_Controller_TestCase  This object.
	 */
	public function makeGetRequest($routeName, $routeParams = array(), $getParams = array())
	{
		$uri = Route::get($routeName)->uri($routeParams);

		try
		{
			$request = Request::factory($uri);
			$this->_response = $request
				->query($getParams)
				->execute();
		}
		catch (RedirectException $e)
		{
			$this->_redirect = json_decode($e->getMessage(), true);
		}
	}

	/**
	 * Make a post request and save the response in $this->response.
	 *
	 * @param  string $routeName
	 * @param  array  $routeParams
	 * @param  array  $getParams
	 * @return Kohana_Unittest_Controller_TestCase  This object.
	 */
	public function makePostRequest($routeName, $routeParams = array(), $postParams = array())
	{
		$uri = Route::get($routeName)->uri($routeParams);

		try
		{
			$request = Request::factory($uri);
			$this->_response = $request
				->method('POST')
				->post($postParams)
				->execute();
		}
		catch (RedirectException $e)
		{
			$this->_redirect = json_decode($e->getMessage(), true);
		}
	}

	/**
	 * Assert that an HTML string has a match for a CSS selector and that it optionally
	 * contains the specified content.
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

		$html   = $response->body();
		$dom    = new Zend_Dom_Query($html);
		$result = $dom->query($selector);

		$this->assertNotEquals(
			0,
			$result->count(),
			'Failed asserting that response body contains selector: '.$selector
		);

		if ($pattern)
		{
			$node = $result->current();
			$selectedHtml = $node->nodeValue;
			$this->assertRegExp($pattern, $selectedHtml);
		}
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
}