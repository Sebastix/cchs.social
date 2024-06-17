<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;
use Drupal\danse_content\Controller\Subscription;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Returns responses for cchs_notifications routes.
 */
final class WebPushSubscription extends Subscription {

  /**
   * List of routes to subscribe.
   */
  const DANSE_SUBSCRIBE_ROUTES = [
    'danse_content.api.subscribe',
    'danse_content.api.unsubscribe',
  ];

  /**
   * {@inheritdoc}
   */
  public function subscribe(string $entity_type, string $entity_id, string $key): AjaxResponse {
    $this->userData->set('danse', $this->currentUser->id(), $key, 1);
    /* @todo Make this request works for WebPush subscription entity save */
    /* $this->webPushSubscribe(); */
    return $this->widget($entity_type, $entity_id);
  }

  /**
   * {@inheritdoc}
   */
  public function unsubscribe(string $entity_type, string $entity_id, string $key): AjaxResponse {
    // For now, just do parent call.
    return parent::unsubscribe($entity_type, $entity_id, $key);
  }

  /**
   * Create or delete WebPush subscription entity.
   */
  private function webPushSubscribe(): void {
    $token_url = Url::fromRoute('system.csrftoken', [], ['absolute' => TRUE])->toString();
    $subscribe_url = Url::fromRoute('rest.web_push_subscription.POST', [], ['absolute' => TRUE])->toString();
    $client = new Client();
    $headers = [
      // 'Authorization' => 'Bearer ' . $token,
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
    ];
    $body_data = [
      'key' => NULL,
      'token' => NULL,
      'endpoint' => NULL,
    ];
    $body = Json::encode($body_data);
    $request = new Request('POST', $subscribe_url, [
      'json' => $body_data,
      'headers' => $headers,
    ]);
    $promise = $client->sendAsync($request)->then(function ($r) {
      // $subscription = $this->entityTypeManager->getStorage('web_push_subscription')->create([
      // 'key' => $key,
      // 'token' => $token,
      // 'endpoint' => $endpoint,
      // ]);
      // $subscription->save();
    });
    $promise->wait();
  }

}
