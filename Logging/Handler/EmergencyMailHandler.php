<?php

namespace Xanweb\C5\Foundation\Logging\Handler;

use Concrete\Core\Config\Repository\Repository as ConfigRepository;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\User\User;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\MailHandler;
use Monolog\Logger;

class EmergencyMailHandler extends MailHandler
{
    public const MAX_EMAILS_PER_HOUR = 10;

    /**
     * Email Address where to send Exceptions.
     *
     * @var string
     */
    protected string $reportEmail;

    /**
     * @var ConfigRepository
     */
    protected ConfigRepository $config;

    /**
     * SendExceptionMailHandler constructor.
     *
     * @param string $reportMailAdr Email Address where to send Exceptions
     */
    public function __construct(string $reportMailAdr)
    {
        parent::__construct(Logger::EMERGENCY, true);

        $this->setFormatter(new HtmlFormatter());
        $this->config = Config::getFacadeRoot();
        $this->reportEmail = $reportMailAdr;
    }

    /**
     * Register Handler using "xanweb.email_logging.report_email" config.
     */
    public static function register(): void
    {
        $app = Application::getFacadeApplication();
        $app['director']->addListener('on_logger_create', function ($le) use ($app) {
            $reportEmail = $app['config']->get('xanweb.email_logging.report_email');
            if (!$reportEmail) {
                throw new \RuntimeException(t('Report Email is not defined in `xanweb.email_logging.report_email` config.'));
            }

            $le->getLogger()->pushHandler(new static($reportEmail));
        });
    }

    protected function send($content, array $records): void
    {
        if (!$this->canSend()) {
            return;
        }

        $app = Application::getFacadeApplication();
        $u = $app->make(User::class);
        $user = t('User: %s', $u->isRegistered() ? $u->getUserName() : t('Guest'));

        $refererURL = t('Referer URL: %s', $_SERVER['HTTP_REFERER'] ?? '');
        $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $method = 'Method: ' . $_SERVER['REQUEST_METHOD'];

        $mh = $app->make('mail');
        $mh->setTesting(true);
        $mh->setSubject($_SERVER['SERVER_NAME'] . ': Exception occurred');
        $mh->setBodyHTML($user . '<br>' . $url . '<br>' . $refererURL . '<br>' . $method . '<br>' . $content);
        $mh->to($this->reportEmail);

        try {
            $mh->sendMail();
        } catch (\Throwable $e) {
        }
    }

    /**
     * Check if limit is reached per hour.
     *
     * @return bool
     */
    protected function canSend(): bool
    {
        $hourStamp = $this->config->get('xanweb.email_logging.hour_stamp', 0);
        $diff = time() - $hourStamp;
        if ($diff > 3600) {
            $this->config->save('xanweb.email_logging.hour_stamp', time());
            $this->config->save('xanweb.email_logging.count', 1);

            return true;
        }

        $sentLogCount = $this->config->get('xanweb.email_logging.count', 0);
        if ($sentLogCount < static::MAX_EMAILS_PER_HOUR) {
            $this->config->save('xanweb.email_logging.count', ++$sentLogCount);

            return true;
        }

        return false;
    }
}
