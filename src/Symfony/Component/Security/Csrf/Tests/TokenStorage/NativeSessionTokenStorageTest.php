<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Csrf\CsrfProvider;

use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @runTestsInSeparateProcesses
 */
class NativeSessionTokenStorageTest extends \PHPUnit_Framework_TestCase
{
    const SESSION_NAMESPACE = 'foobar';

    /**
     * @var NativeSessionTokenStorage
     */
    private $storage;

    public static function setUpBeforeClass()
    {
        ini_set('session.save_handler', 'files');
        ini_set('session.save_path', sys_get_temp_dir());

        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
        $_SESSION = array();

        $this->storage = new NativeSessionTokenStorage(self::SESSION_NAMESPACE);
    }

    public function testStoreTokenInClosedSession()
    {
        $this->storage->setToken('token_id', 'TOKEN');

        $this->assertSame(array(self::SESSION_NAMESPACE => array('token_id' => 'TOKEN')), $_SESSION);
    }

    public function testStoreTokenInClosedSessionWithExistingSessionId()
    {
        session_id('foobar');

        $this->assertSame(PHP_SESSION_NONE, session_status());

        $this->storage->setToken('token_id', 'TOKEN');

        $this->assertSame(PHP_SESSION_ACTIVE, session_status());
        $this->assertSame(array(self::SESSION_NAMESPACE => array('token_id' => 'TOKEN')), $_SESSION);
    }

    public function testStoreTokenInActiveSession()
    {
        session_start();

        $this->storage->setToken('token_id', 'TOKEN');

        $this->assertSame(array(self::SESSION_NAMESPACE => array('token_id' => 'TOKEN')), $_SESSION);
    }

    /**
     * @depends testStoreTokenInClosedSession
     */
    public function testCheckToken()
    {
        $this->assertFalse($this->storage->hasToken('token_id'));

        $this->storage->setToken('token_id', 'TOKEN');

        $this->assertTrue($this->storage->hasToken('token_id'));
    }

    /**
     * @depends testStoreTokenInClosedSession
     */
    public function testGetExistingToken()
    {
        $this->storage->setToken('token_id', 'TOKEN');

        $this->assertSame('TOKEN', $this->storage->getToken('token_id'));
    }

    public function testGetNonExistingToken()
    {
        $this->assertSame('DEFAULT', $this->storage->getToken('token_id', 'DEFAULT'));
    }
}
