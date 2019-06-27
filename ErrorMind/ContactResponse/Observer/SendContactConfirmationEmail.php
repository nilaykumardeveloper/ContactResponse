<?php
/**
 * Class SendContactConfirmationEmail
 * @object Send an email confirmation to the user after submit a contact form
 */

namespace ErrorMind\ContactResponse\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SendContactConfirmationEmail implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * For logger, var/log
     */
    protected $_logger;



    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_request = $request;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
    }


    public function execute(EventObserver  $observer)
    {
        $event = $observer->getEvent();
        $name = $event->getRequest()->getPost()['name'];
        $email = $event->getRequest()->getPost()['email'];
        $content = $event->getRequest()->getPost()['comment'];

        if ($email) {
            try {
                $store = $this->_storeManager->getStore()->getId();
                $transport = $this->_transportBuilder->setTemplateIdentifier('contact_confirmation_template')
                    ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
                    ->setTemplateVars(
                        [
                            'store' => $this->_storeManager->getStore(),
                            'name' => $name,
                            'email' => $email,
                            'content' => $content,
                        ]
                    )
                    ->setFrom('general') //Store -> Configuration -> General -> Store Email Addresses
                    ->addTo($email, $name)
                    ->getTransport();
                $transport->sendMessage();
                //$this->_logger->info("Message sent !");
                return;
            } catch (\Exception $e) {
                $this->_logger->critical($e->getMessage());
                return;
            }
        }
    }
}