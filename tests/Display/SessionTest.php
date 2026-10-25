<?php

declare(strict_types=1);

namespace Skeet\Tests\Display;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../skeet/lib/skeet/session/abstract_session.class.php';

/** Caching session with no real backend, to exercise the base cache logic. */
final class FixtureSession extends \Skeet\Session\AbstractSession
{
    protected bool $doCacheSessionVariables = true;
    protected function open(): void {}
}

final class SessionTest extends TestCase
{
    public function testSetGetDelete(): void
    {
        $session = new FixtureSession();
        $session->set('user_id', 42);
        $this->assertSame(42, $session->get('user_id'));

        $session->delete('user_id');
        $this->assertNull($session->get('user_id'));
    }

    public function testMissingKeyIsNull(): void
    {
        $this->assertNull((new FixtureSession())->get('nope'));
    }
}
