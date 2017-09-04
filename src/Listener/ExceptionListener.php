<?php
namespace CurrencyRates\Listener;

use Monolog\Logger;
use CurrencyRates\Exception\TranslatedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExceptionListener
{
    private $translator;
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function __construct($translator, Logger $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $message = sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        $this->logException($exception, $message);

        // Customize your response object to display the exception details
        $response = new JsonResponse();
        $classname = get_class($exception);
        if ($pos = strrpos($classname, '\\')) {
            $classname = substr($classname, $pos + 1);
        }

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof TranslatedException) {
            $response->setData([
                'errors' => [
                    [
                        'code' => $exception->getStatusCode(),
                        'title' => $classname,
                        'detail' => $exception->getMessage(),
                        'error_code' => $exception->getErrorCode(),
                    ]
                ]
            ]);
            $event->setResponse($response);
            return;
        } elseif ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
            $response->setData([
                'errors' => [
                    [
                        'code' => $response->getStatusCode(),
                        'title' => $classname,
                        'detail' => $this->translator->trans($exception->getMessage()),
                        'error_code' => $exception->getMessage(),
                    ]
                ]
            ]);
            $event->setResponse($response);
            return;
        } elseif ($exception instanceof AccessDeniedException) {
            $response->setStatusCode($exception->getCode());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->setData([
            'code' => $response->getStatusCode(),
            'title' => $classname,
            'detail' => $exception->getMessage(),
            'backtrace' => $exception->getTrace(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'errors' => [
                [
                    'code' => $response->getStatusCode(),
                    'title' => $classname,
                    'detail' => $exception->getMessage()
                ]
            ]
        ]);

        // Send the modified response object to the event
        $event->setResponse($response);
    }

    /**
     * Logs an exception.
     *
     * @param \Exception $exception The \Exception instance
     * @param string     $message   The error message to log
     */
    protected function logException(\Exception $exception, $message)
    {
        if (null !== $this->logger) {
            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message, array('exception' => $exception));
            } else {
                $this->logger->error($message, array('exception' => $exception));
            }
        }
    }
}
