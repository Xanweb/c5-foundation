<?php
namespace Xanweb\Foundation\Request;

use Concrete\Core\User\User as ConcreteUser;
use Concrete\Core\Support\Facade\Application;

/**
 * Class User
 *
 * @method static bool isRegistered()
 * @method static bool isSuperUser()
 * @method static string getUserName()
 */
class User
{
    private static $instance;

    /**
     * @var ConcreteUser
     */
    private $user;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct()
    {
        $app = Application::getFacadeApplication();
        $this->user = $app->make(ConcreteUser::class);
    }

    public static function canAccessDashboard(): bool
    {
        $ru = self::get();

        return $ru->cache['canAccessDashboard'] ?? ($ru->cache['canAccessDashboard'] = ($ru->user->isRegistered() && c5app('helper/concrete/dashboard')->canRead()));
    }

    public function __call($name, $arguments)
    {
        return $this->user->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::get()->user->$name(...$arguments);
    }

    /**
     * Gets a singleton instance of this class.
     */
    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}