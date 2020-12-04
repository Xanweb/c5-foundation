<?php
namespace Xanweb\Foundation\Block\Traits;

use Concrete\Core\Block\Block;
use Concrete\Core\Permission\Checker as Permissions;
use Concrete\Core\Session\SessionValidator;
use Concrete\Core\Utility\Service\Validation\Numbers;
use Illuminate\Support\Str;

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
    protected $realIdentifier;
    protected $uniqID;

    /**
     * Get Uniq Identifier for Block.
     */
    public function getUniqueId(): string
    {
        if (!$this->uniqID) {
            $prefix = strtolower($this->getRealIdentifier());
            $this->uniqID = $prefix . '_' . Str::quickRandom(3);
        }

        return $this->uniqID;
    }

    /**
     * Get Uniq Identifier for Block.
     */
    public function getRealIdentifier(): string
    {
        if (!$this->realIdentifier) {
            $b = $this->getBlockObject(); /* @var Block $b */
            if (is_object($b) && $proxyBlock = $b->getProxyBlock()) {
                $this->realIdentifier = (string) $proxyBlock->getController()->getIdentifier();
            } else {
                $this->realIdentifier = (string) $this->getIdentifier();
            }
        }

        return $this->realIdentifier;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::isValidControllerTask()
     */
    public function isValidControllerTask($method, $parameters = [])
    {
        if (parent::isValidControllerTask($method, $parameters)) {
            $bID = array_pop($parameters);
            if ((new Numbers())->integer($bID, 1, PHP_INT_MAX) && (int) $this->bID === (int) $bID) {
                return true;
            }
        }

        return false;
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
     * @see \Concrete\Core\Block\BlockController::getSets()
     */
    public function getSets()
    {
        $sets = parent::getSets();

        $validator = $this->app->make(SessionValidator::class);
        if ($validator->hasActiveSession()) {
            $blockIdentifier = $this->getRealIdentifier();
            $sessionBag = $this->app->make('session')->getFlashBag();
            if ($sessionBag->has('block_message_' . $blockIdentifier)) {
                $messages = $sessionBag->get('block_message_' . $blockIdentifier);
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
        $session->getFlashBag()->add('block_message_' . $this->getRealIdentifier(), [$key, $value, $isHTML]);
    }
}