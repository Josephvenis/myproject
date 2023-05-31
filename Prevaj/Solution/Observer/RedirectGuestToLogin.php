<?php

namespace Prevaj\Solution\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CustomerRestriction
 * @package Steven\Restriction\Observer
 */
class RedirectGuestToLogin implements ObserverInterface
{
    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CustomerRestriction constructor.
     * @param Session $customerSession
     * @param RedirectInterface $redirect
     * @param State $state
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $customerSession,
        RedirectInterface $redirect,
        State $state,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->state = $state;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        // Only restrict the frontend pages
        if ($this->getArea() === 'frontend') {
            $controllerName = $observer->getEvent()->getRequest()->getControllerName();
            $controller = $observer->getControllerAction();

            // Allow homepage
            if ($observer->getEvent()->getRequest()->getFullActionName() === 'prevaj-custom-page') {
                return $this;
            }

            // Allow customer pages, customer actions (sign in, sign up, reset password, etc...) and sections (cart sections, customer sections, etc...)
            if ($controllerName === 'account' || $controllerName === 'section') {
                return $this;
            }

            // Redirect to login page if customer is not logged in
            if (!$this->customerSession->isLoggedIn()) {
                $this->redirect->redirect($controller->getResponse(), 'customer/account/login');
            }
        }

        return $this;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    private function getArea()
    {
        return $this->state->getAreaCode();
    }
}