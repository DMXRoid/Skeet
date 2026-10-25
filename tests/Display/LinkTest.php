<?php

declare(strict_types=1);

namespace Skeet\Tests\Display;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../skeet/lib/skeet/link/abstract_link.class.php';
require_once __DIR__ . '/../../skeet/lib/skeet/link/generic_link.class.php';

if (!defined('INDEX_PAGE')) {
    define('INDEX_PAGE', 'index.php');
}

final class LinkTest extends TestCase
{
    public function testFileNameDerivedFromStudlyPageName(): void
    {
        $link = new \Skeet\Link\GenericLink('SomePage');
        $this->assertSame('some/page', $link->getFileName());
    }

    public function testLinkWithArgs(): void
    {
        $link = new \Skeet\Link\GenericLink('SomePage');
        $this->assertSame($link, $link->addLinkArg('a', '1')->addLinkArg('b', '2')); // chainable
        $url = $link->getLink();
        $this->assertStringContainsString('a=1', $url);
        $this->assertStringContainsString('b=2', $url);

        $link->removeLinkArg('a');
        $this->assertStringNotContainsString('a=1', $link->getLink());
    }

    public function testLinkAsFormRendersHiddenInputs(): void
    {
        $link = new \Skeet\Link\GenericLink('SomePage');
        $form = $link->addLinkArg('token', 'abc')->getLinkAsForm();
        $this->assertStringContainsString('<form', $form);
        $this->assertStringContainsString('name="token"', $form);
        $this->assertStringContainsString('value="abc"', $form);
    }
}
