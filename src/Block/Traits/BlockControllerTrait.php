<?php
namespace Xanweb\Foundation\Block\Traits;

use Concrete\Core\Block\Block;
use Concrete\Core\Permission\Checker as Permissions;
use Concrete\Core\Session\SessionValidator;

/**
 * Trait BlockControllerTrait.
 *
 * @property \Concrete\Core\Application\Application $app
 * @property \Concrete\Core\Http\Request $request
 * @property Block $block
 * @property int $bID
 */
trait BlockControllerTrait
{
    protected $uniqID;

    /**
     * Get Uniq Identifier for Block.
     */
    public function getUniqueId(): string
    {
        if (!$this->uniqID) {
            $prefix = strtolower($this->getIdentifier());
            $b = $this->getBlockObject(); /* @var Block $b */
            if (is_object($b) && $b->getProxyBlock()) {
                $prefix = strtolower($b->getProxyBlock()->getController()->getIdentifier());
            }

            $this->uniqID = $prefix . $this->app['helper/validation/identifier']->getString(4);
        }

        return $this->uniqID;
    }

    /**
     * {@inheritdoc}
     *
     * @see CoreBlockController::isValidControllerTask()
     */
    public function isValidControllerTask($method, $parameters = [])
    {
        $result = false;
        if (parent::isValidControllerTask($method, $parameters)) {
            $bID = array_pop($parameters);
            if (is_int($bID) || (is_string($bID) && is_numeric($bID))) {
                if ($this->bID === (int) $bID) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * return current block area.
     */
    protected function getCurrentAreaName(): string
    {
        if ($this->block instanceof Block) {
            $areaName = $this->block->getAreaHandle();
        } else {
            $areaName = $this->request->get('arHandle');
        }

        return $areaName;
    }

    /**
     * Check if active user can edit block.
     */
    public function userCanEditBlock(): bool
    {
        if (!is_object($this->block)) {
            return false;
        }

        $bp = new Permissions($this->block);

        return $bp->canWrite();
    }

    /**
     * {@inheritdoc}
     *
     * @see CoreBlockController::getSets()
     */
    public function getSets()
    {
        $sets = parent::getSets();

        $validator = $this->app->make(SessionValidator::class);
        if ($validator->hasActiveSession()) {
            $sessionBag = $this->app->make('session')->getFlashBag();
            if ($sessionBag->has('block_message_' . $this->bID)) {
                $messages = $sessionBag->get('block_message_' . $this->bID);
                foreach ($messages as [$key, $value, $isHTML]) {
                    $sets[$key] = $value;
                    $sets[$key . 'IsHTML'] = $isHTML;
                }
            }
        }

        return $sets;
    }

    public function flash(string $key, string $value, bool $isHTML = false): void
    {
        $session = $this->app->make('session');
        $session->getFlashBag()->add('block_message_' . $this->bID, [$key, $value, $isHTML]);
    }
}
