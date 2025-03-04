<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 2], // Priority higher than Symfony's ExceptionListener
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        // Handle 404 errors
        if ($exception instanceof NotFoundHttpException) {
            $content = $this->twig->render('exception/error404.html.twig');
            
            $response = new Response();
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            
            $event->setResponse($response);
        }
    }
} 