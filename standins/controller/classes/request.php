<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Overrides the core Request in order to make redirects testable, and make it so the
 * redirect method does not call "exit", which halts execution of the test runner.
 */
class Request extends Kohana_Request
{
	/**
	 * Instead of carrying out the redirect and exiting, throw an exception
	 * whose message is the JSON of an array containing the url and status code.
	 *
	 * @throws  RedirectException
	 * @param   string  $url  string
	 * @param   integer $code int
	 */
	public function redirect($url = '', $code = 302)
	{
		$redirect = array(
			'url'  => $url,
			'code' => $code
		);
		throw new RedirectException(json_encode($redirect));
	}
}

/**
 * Custom exception to prevent code after the redirect from running without
 * resorting to calling "exit".
 */
class RedirectException extends Exception {}
