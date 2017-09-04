<?php

namespace CurrencyRates\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use CurrencyRates\Entity\User;

class IndexController extends BaseController
{
    /**
     * @Route   ("/")
     * @Method  ("GET")
     * You can use this to check your server health
     */
    public function indexAction(Request $request)
    {
        return new JsonResponse('ok');
    }
}
