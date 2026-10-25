<?php
	/**
	 * Router for PHP's built-in web server (dev only).
	 *
	 * Serves real files directly; routes everything else to the docroot's
	 * front controller (index.php), exposing the path as REDIRECT_URL the way a
	 * rewrite rule would in production.
	 */

	declare(strict_types=1);

	$docroot = $_SERVER['DOCUMENT_ROOT'];
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
	$file = $docroot . $path;

	if ($path !== '/' && is_file($file)) {
		return false; // let the built-in server serve the static asset
	}

	$_SERVER['REDIRECT_URL'] = $path;
	require $docroot . '/index.php';
