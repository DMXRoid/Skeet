<?php
	declare(strict_types=1);

	namespace Skeet\Page;

	class GenericPage extends AbstractPage {
		public function __construct(string $pageName) {
			$this->pageName = $pageName;
			parent::__construct();
		}
	}
