<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Route subscriber.
 */
final class DanseRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {

    if ($route = $collection->get('danse_content.api.subscribe')) {
      $route->setDefault('_controller', '\Drupal\cchs_notifications\Controller\WebPushSubscription::subscribe');
    }
    if ($route = $collection->get('danse_content.api.unsubscribe')) {
      $route->setDefault('_controller', '\Drupal\cchs_notifications\Controller\WebPushSubscription::unsubscribe');
    }
  }

}
