<?php
namespace Xanweb\Foundation\Block\Traits;

use Concrete\Core\Block\Block;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Permission\Checker as Permissions;
use Concrete\Core\Session\SessionValidator;
use Concrete\Core\Utility\Service\Validation\Numbers;
use Illuminate\Support\Str;
use Xanweb\Foundation\Request\User as RequestUser;
use Xanweb\Foundation\Request\Page as RequestPage;

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
    /**
     * @var Page
     */
    private $blockPage;

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

    protected function getCurrentAreaName(): ?string
    {
        $areaName = ($this->block instanceof Block) ? $this->block->getAreaHandle() : null;

        return $areaName ?? $this->request->get('arHandle');
    }

    /**
     * Check whether we are in Edit Mode.
     *
     * @return bool
     */
    public function isInEditMode(): bool
    {
        $c = $this->getPageObject();
        if ($c !== null) {
            return $c->isEditMode();
        }

        return RequestPage::isEditMode();
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

    /**
     * Get Block Related Page Language.
     *
     * @return string
     */
    public function getPageLanguage(): string
    {
        static $language;

        if (!$language && ($page = $this->getPageObject()) !== null) {
            $section = Section::getBySectionOfSite($page);
            if (is_object($section)) {
                $language = $section->getLanguage();
            }
        }

        return $language ?? RequestPage::getLanguage();
    }

    /**
     * Get Block Related Page Locale.
     *
     * @return string
     */
    public function getPageLocale(): string
    {
        static $locale;

        if (!$locale && ($page = $this->getPageObject()) !== null) {
            $section = Section::getBySectionOfSite($page);
            if (is_object($section)) {
                $locale = $section->getLocale();
            }
        }

        return $locale ?? RequestPage::getLocale();
    }

    /**
     * Get Block Related Page Object.
     *
     * @return Page
     */
    public function getPageObject(): ?Page
    {
        if (!$this->blockPage) {
            if ($this->isEditedWithinStack()) {
                $this->blockPage = $this->getCollectionObject() ?: null;
            } else {
                $c = $this->request->getCurrentPage();
                if ($c instanceof Page && !$c->isError()) {
                    $this->blockPage = $c;
                } else {
                    $this->blockPage = $this->getCollectionObject() ?: null;
                }
            }
        }

        return $this->blockPage;
    }

    /**
     * Check if the block is edited in Stack.
     *
     * @return bool
     */
    public function isEditedWithinStack(): bool
    {
        if (RequestUser::canAccessDashboard() && !empty($path = $this->request->getPath())
            && $this->request->matches('*' . STACKS_LISTING_PAGE_PATH . '*')) {

            $cID = (string) last(explode('/', $path));
            if (strpos($cID, '@') !== false) {
                list($cID, $locale) = explode('@', $cID, 2);
            }

            if ($cID > 0) {
                $s = Stack::getByID($cID);

                return is_object($s);
            }
        }

        return false;
    }
}
