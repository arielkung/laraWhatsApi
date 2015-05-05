<?php namespace Williamson\Larawhatsapi\Clients;

use WhatsProt;
use Williamson\Larawhatsapi\Repository\SMSMessageInterface;

class LaraWhatsapiMGP25Client implements SMSMessageInterface{

    /**
     * @var string $password
     */
    protected $password;

    /**
     * @var WhatsProt
     */
    protected $whatsProt;

    /**
     * @param WhatsProt $whatsProt
     */
    public function __construct(WhatsProt $whatsProt)
    {
        $this->whatsProt = $whatsProt;
        $account   = config("larawhatsapi.useAccount");
        $this->password = config("larawhatsapi.accounts.$account.password");
    }

    public function sendMessage($to, $message)
    {
        $this->connectAndLogin();
        $this->whatsProt->sendMessageComposing($to);
        $this->whatsProt->sendMessage($to, $message);
        $this->logoutAndDisconnect();
    }

    public function checkForNewMessages()
    {
        $this->connectAndLogin();
        $time = time();
        while (true) {
            $this->whatsProt->pollMessage();

            if (time() - $time >= 10) {
                $time = time();
                $this->whatsProt->sendActiveStatus();
            }

            usleep(1000);
        }

        $this->logoutAndDisconnect();
    }

    protected function connectAndLogin()
    {
        $this->whatsProt->connect();
        $this->whatsProt->loginWithPassword($this->password);
    }

    protected function logoutAndDisconnect()
    {
        $this->whatsProt->disconnect();
    }
}