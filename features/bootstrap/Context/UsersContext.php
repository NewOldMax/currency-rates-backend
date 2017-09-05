<?php

namespace features\CurrencyRates\Context;

class UsersContext extends CommonContext
{

    /**
     * @Given I want to logout
     */
    public function iWantToLogout()
    {
        $this->get('/api/logout');
    }

    /**
     * @Given I want to get access to server
     */
    public function iWantToGetAccessToServer()
    {
        $this->get('/api/health');
    }
}
