<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\EventSubscriber;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribe to DANSE subscribe routes.
 */
final class DanseSubscribeSubscriber implements EventSubscriberInterface {

  /**
   * List of routes to subscribe.
   */
  const DANSE_SUBSCRIBE_ROUTES = [
    'danse_content.api.subscribe',
    'danse_content.api.unsubscribe',
  ];

  /**
   * Kernel request event handler.
   */
  public function onKernelRequest(RequestEvent $event): void {}

  /**
   * Kernel response event handler.
   */
  public function onKernelResponse(ResponseEvent $event): void {

    $response = $event->getResponse();
    $path = $event->getRequest()->getPathInfo();
    $url = Url::fromUserInput($path);
    $do = $url->isRouted() && in_array($url->getRouteName(), static::DANSE_SUBSCRIBE_ROUTES) && $response instanceof AjaxResponse;

    if ($do) {
      $subscribeUrl = Url::fromRoute('rest.web_push_subscription.POST')->toString();
      // $route_name = $url->getRouteName();
      // $route_params = $url->getRouteParameters();
      // Unsubscribe, delete subscription.
      if ($route_name == static::DANSE_SUBSCRIBE_ROUTES[1]) {
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onKernelRequest'],
      KernelEvents::RESPONSE => ['onKernelResponse'],
    ];
  }

}
