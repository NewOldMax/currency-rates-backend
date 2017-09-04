<?php

namespace CurrencyRates\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Request\ParamFetcher;
use CurrencyRates\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use CurrencyRates\Entity\BasicEntity;
use CurrencyRates\Service\Http\CustomResponse;

class BaseController extends Controller
{

    protected function viewError($messages, $code = 500)
    {
        return new CustomResponse(
            [
                'errors' => [
                    [
                        'code' => $code,
                        'title' => 'Exception',
                        'detail' => $messages,
                    ]
                ]
            ],
            $code
        );
    }

    protected function viewTranslatedError($message, $code = 500)
    {
        return new CustomResponse(
            [
                'errors' => [
                    [
                        'code' => $code,
                        'title' => 'TranslatedException',
                        'detail' => $this->get('translator')->trans($message),
                        'error_code' => $message
                    ]
                ]
            ],
            $code
        );
    }

    protected function viewCollection($key, array $entities, $include = false, $code = 200, $raw = false, $headers = [])
    {
        $result = [];
        foreach ($entities as $value) {
            if (is_object($value)) {
                if (!$value->isMerged()) {
                    $result []= $value->toArray($include);
                }
            } else {
                $result []= $value;
            }
        }
        if ($raw) {
            return $result;
        }
        $response = [$key => $result];
        return new CustomResponse($response, $code, $headers);
    }

    protected function viewCollections($key, array $entities, $include = false, $code = 200, $headers = [])
    {
        $result = [];
        foreach ($entities as $itemKey => $value) {
            $result[$itemKey] = $this->viewCollection($itemKey, $value, $include, $code, $raw = true);
        }
        $response = [$key => $result];
        return new CustomResponse($response, $code, $headers);
    }

    protected function viewItem($key, $entity, $include = false, $code = 200)
    {
        if (is_object($entity)) {
            $result = $entity->toArray($include);
        } else {
            $result = $entity;
        }
        $response = [$key => $result];
        return new CustomResponse($response, $code);
    }

    protected function viewValidationErrors(ConstraintViolationListInterface $errors, $code = 400)
    {
        $message = [];
        foreach ($errors as $error) {
            $message[] = $error->getMessage().'(' .$error->getPropertyPath() .')';
        }
        
        return new CustomResponse(
            [
                'errors' => $message
            ],
            $code
        );
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        $em = $this->getDoctrine()->getManager();
        if (!$em->isOpen()) {
            $em = $em->create($em->getConnection(), $em->getConfiguration());
        }
        return $em;
    }

    protected function getCriteriaQuery(ParamFetcher $fetcher)
    {
        $query = $fetcher->all();
        return $query;
    }

    /**
     * @return User
     * @throws \Exception
     */
    protected function getAuthenticatedUser()
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \Exception("Impossible to get authenticated user on public resource");
        }

        return $user;
    }
}
