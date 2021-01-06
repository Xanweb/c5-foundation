<?php
namespace Xanweb\Foundation\Traits;

use Concrete\Core\Support\Facade\Application;

trait ApplicationTrait
{
    /**
     * @var \Concrete\Core\Application\Application
     */
    protected $app;

    /**
     * @param string $make [optional]
     *
     * @return \Concrete\Core\Application\Application|mixed
     */
    protected function app($make = null)
    {
        if (!$this->app) {
            $this->app = Application::getFacadeApplication();
        }

        if ($make !== null) {
            return $this->app->make($make);
        }

        return $this->app;
    }
}
