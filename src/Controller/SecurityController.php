<?php

namespace CurrencyRates\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use CurrencyRates\Entity\User;
use CurrencyRates\Entity\RefreshToken;
use CurrencyRates\Entity\Token;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class SecurityController extends BaseController
{
    /**
     * @Route   ("/logout")
     * @Method  ("GET")
     */
    public function logoutAction(Request $request)
    {
        $token = $this->get('security.token_storage')->getToken()->getCredentials();
        $em = $this->getEntityManager();
        try {
            $token = $this->get('app_token_manager')->addToBlackList($token);
            $em->persist($token);
            $em->flush();
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route   ("/token/refresh")
     * @Method  ("POST")
     */
    public function refreshTokenAction(Request $request)
    {
        $jwtToken = $request->get('token', false);
        $refreshToken = $request->get('refreshToken', false);
        if ($jwtToken && $refreshToken) {
            if ($this->get('app_token_manager')->inBlackList($jwtToken)) {
                $msg = $this->get('translator')->trans('jwt.errors.token_in_blacklist');
                return $this->viewError([$msg], Response::HTTP_BAD_REQUEST);
            }
            $encodedData = $this->decodeJWT($jwtToken);
            $em = $this->getEntityManager();
            $manager = $this->get('app_refresh_token_manager');
            $token = $manager->get($refreshToken);
            if ($token->getUserId() != $encodedData['id']) {
                $msg = $this->get('translator')->trans('refresh_token.errors.refresh_foreign');
                return $this->viewError([$msg], Response::HTTP_BAD_REQUEST);
            }
            if ($token->isValid()) {
                $userManager = $this->get('app_user_manager');
                $user = $userManager->get($token->getUserId());
                $token = $this->encodeJWT($user);
                return new JsonResponse(['token' => $token], Response::HTTP_CREATED);
            } else {
                $manager->delete($token);
                $em->flush();
                $msg = $this->get('translator')->trans('refresh_token.info.expired_removed');
                return $this->viewError([$msg], Response::HTTP_BAD_REQUEST);
            }
        }
        $msg = $this->get('translator')->trans('refresh_token.errors.tokens_must_provided');
        return $this->viewError([$msg], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Logout from all devices action
     *
     * @Route   ("/token/remove-refresh")
     * @Method  ("GET")
     */
    public function removeRefreshTokenAction(Request $request)
    {
        $em = $this->getEntityManager();
        $manager = $this->get('app_refresh_token_manager');
        $user = $this->getUser();
        if ($tokens = $em->getRepository(RefreshToken::class)->findBy(['user' => $user])) {
            foreach ($tokens as $key => $value) {
                $manager->delete($value);
            }
        }
        $this->get('app_user_manager')->logoutFromAllDevices($user);
        $em->flush();
        $token = $this->encodeJWT($user);
        return new JsonResponse(['token' => $token]);
    }

    public function getManager()
    {
        return $this->get('app_user_manager');
    }

    private function encodeJWT(User $user)
    {
        $encoder = $this->get('app_jwt_encoder');
        return $encoder->encode($user->toAuthArray() + ['sub' => 'user']);
    }

    private function decodeJWT(string $token)
    {
        $encoder = $this->get('app_jwt_encoder');
        return $encoder->decode($token);
    }

    private function getOrCreateRefreshToken(User $user = null)
    {
        if ($user) {
            $manager = $this->get('app_refresh_token_manager');
            $em = $this->getEntityManager();
            $token = null;
            if ($tokens = $em->getRepository(RefreshToken::class)->findBy(['user' => $user])) {
                foreach ($tokens as $key => $value) {
                    if ($value->isValid()) {
                        $token = $value;
                        break;
                    }
                }
            }
            if (!$token) {
                $token = $manager->create($user);
                $em->persist($token);
                $em->flush();
            }
            return $token->getToken();
        }
    }
}
