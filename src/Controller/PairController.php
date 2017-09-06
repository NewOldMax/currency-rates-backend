<?php

namespace CurrencyRates\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use CurrencyRates\Entity\Pair;
use CurrencyRates\Entity\User;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class PairController extends BaseController
{
    /**
     * @Route   ("/pairs")
     * @Method  ("GET")
     */
    public function getPairsAction(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $em = $this->getEntityManager();
        $pairs = $em->getRepository(Pair::class)->findByUser($user);
        return $this->viewCollection('pairs', $pairs);
    }

    /**
     * @Route   ("/pairs")
     * @Method  ("POST")
     */
    public function createPairAction(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser();
            $data = $request->request->all();
            $data['user'] = $user;

            $pair = $this->getManager()->create($data);
            $em = $this->getEntityManager();
            $em->persist($pair);
            $em->flush();
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        
        return $this->viewItem('pair', $pair, false, Response::HTTP_CREATED);
    }

    /**
     * @Route   ("/pairs/{id}")
     * @Method  ("GET")
     */
    public function getPairAction($id, Request $request)
    {
        $this->checkAccess($this->getAuthenticatedUser(), $id);
        $pair = $this->getManager()->get($id);
        
        return $this->viewItem('pair', $pair);
    }

    /**
     * @Route   ("/pairs/{id}")
     * @Method  ("PATCH")
     */
    public function updatePairAction($id, Request $request)
    {
        $this->checkAccess($this->getAuthenticatedUser(), $id);
        
        try {
            $data = $request->request->all();

            $pair = $this->getManager()->update($id, $data);
            $em = $this->getEntityManager();
            $em->flush();
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem('pair', $pair);
    }

    /**
     * @Route   ("/pairs/{id}")
     * @Method  ("DELETE")
     */
    public function removePairAction($id, Request $request)
    {
        $this->checkAccess($this->getAuthenticatedUser(), $id);

        $this->getManager()->delete($id);
        $em = $this->getEntityManager();
        $em->flush();
        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route   ("/pairs/{id}/historical")
     * @Method  ("GET")
     */
    public function getHistoricalInfoAction($id, Request $request)
    {
        $this->checkAccess($this->getAuthenticatedUser(), $id);

        $rates = $this->getManager()
            ->setRateManager($this->getRateManager())
            ->getHistoricalInfo($id);
        return $this->viewCollection('rates', $rates);
    }

    private function checkAccess(User $user, $pairId)
    {
        $pair = $this->getManager()->get($pairId);
        if ($pair->getUser() != $user) {
            throw $this->createAccessDeniedException('user.errors.access_denied');
        }
    }

    public function getManager()
    {
        return $this->get('app_pair_manager');
    }

    public function getRateManager()
    {
        return $this->get('app_currency_rate_manager');
    }
}
