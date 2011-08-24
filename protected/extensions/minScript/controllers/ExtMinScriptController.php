<?php

/**
 * minScript Controller.
 *
 * Serve combined, minified and compressed files with cache headers.
 *
 * @package ext.minScript.controllers
 * @author TeamTPG
 * @copyright Copyright &copy; 2011 TeamTPG
 * @license BSD 3-clause
 * @link http://code.teamtpg.ch/minscript
 * @version 1.0.5
 */
class ExtMinScriptController extends CExtController {
	/**
	 * Serve files.
	 */
	public function actionServe() {
		require (dirname(dirname(__FILE__)) . '/vendors/minify/min/index.php');
	}

	/**
	 * Ensure that everything is prepared before we execute the serve action.
	 * @param CFilterChain $filterChain Instance of CFilterChain.
	 */
	public function filterValidateServe($filterChain) {
		header('X-Powered-By:');
		header('Pragma:');
		header('Expires:');
		header('Cache-Control:');
		header('Last-Modified:');
		header('Etag:');
		header('Content-Encoding:');
		header('Content-Type:');
		header('Content-Length:');
		@ob_end_clean();
		if(isset($_GET['g'])) {
			$qs = 'g=' . $_GET['g'];
			foreach($_GET as $key => $value) {
				if(ctype_digit((string)$key)) {
					$qs .= '&' . $key;
					break;
				}
			}
			$_SERVER['QUERY_STRING'] = $qs;
		}
		$filterChain -> run();
	}

	/**
	 * Execute filters.
	 * @return array Filters to execute.
	 */
	public function filters() {
		return array('validateServe + serve', );
	}

}
