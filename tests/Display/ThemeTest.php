<?php

declare(strict_types=1);

namespace Skeet\Tests\Display;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../skeet/lib/skeet/theme/abstract_theme.class.php';

final class FixtureTheme extends \Skeet\Theme\AbstractTheme
{
    protected string $cssURL = '/css/';
    /** @var string[] */
    protected array $cssToLoad = ['a.css', 'b.css'];
    protected string $javascriptURL = '/js/';
    /** @var string[] */
    protected array $javascriptToLoad = ['x.js'];
}

final class ThemeTest extends TestCase
{
    public function testRenderCss(): void
    {
        $css = (new FixtureTheme())->renderCSS();
        $this->assertStringContainsString('<link rel="stylesheet" href="/css/a.css">', $css);
        $this->assertStringContainsString('/css/b.css', $css);
    }

    public function testRenderJavascript(): void
    {
        $js = (new FixtureTheme())->renderJavascript();
        $this->assertStringContainsString('<script src="/js/x.js"></script>', $js);
    }

    public function testAccessors(): void
    {
        $theme = new FixtureTheme();
        $this->assertSame(['a.css', 'b.css'], $theme->getCSSToLoad());
        $this->assertSame('/js/', $theme->getJavascriptURL());
    }
}
