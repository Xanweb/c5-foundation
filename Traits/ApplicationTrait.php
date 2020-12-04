<?php
namespace Xanweb\Foundation\Traits;

use Concrete\Core\Application\Application;
use Concrete\Core\Support\Facade\Facade;

trait ApplicationTrait
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param string $make [optional]
     *
     * @return Application|mixed
     */
    protected function app($make = null)
    {
        if (!$this->app) {
            $this->app = Facade::getFacadeApplication();
        }

        if ($make !== null) {
            return $this->app->make($make);
        }

        return $this->app;
    }
}
