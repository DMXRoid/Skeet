<?php
	/*
		database_exception.class.php

		General framework-level database error. PDO raises \PDOException for
		driver errors; this wraps higher-level database failures (pass the
		underlying throwable as $previous where relevant).

		Emailing is on: database errors usually mean a fundamental break, not
		simple user error.
	*/

	declare(strict_types=1);

	namespace Skeet\Exception;

	class DatabaseException extends AbstractException {
		protected bool $doEmail = true;
	}
