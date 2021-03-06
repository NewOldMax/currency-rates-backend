<?php

namespace CurrencyRates\Service\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class CustomResponse extends JsonResponse
{
    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return JsonResponse
     */
    protected function update()
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }

        // Only set the header when there is none
        // or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        if (!$this->headers->has('Date')) {
            $this->setDate(\DateTime::createFromFormat('U', time()));
        }

        return $this->setContent($this->data);
    }
}
