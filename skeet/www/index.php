<?php
	/*
		index.php

		The front controller. Load the application bootstrap, resolve the
		requested page, and render it.
	*/

	namespace Skeet;

	try {
		require_once("../application.inc.php");
		try {
			if (!getRequestValue(PAGE_REQUEST_KEY)) {
				$redirectUrl = $_SERVER["REDIRECT_URL"] ?? "/";
				$_REQUEST[PAGE_REQUEST_KEY] = ($redirectUrl !== "/")
					? strtolower(trim(str_replace("/", " ", $redirectUrl)))
					: "Home";
			}
			PageFactory::getPage(getRequestValue(PAGE_REQUEST_KEY))->render();
		}
		catch (\Exception $e) {
			$e->processException();
		}
	}
	catch (\Exception $e) {
		echo "We're sorry, but an error has occurred.";
	}
