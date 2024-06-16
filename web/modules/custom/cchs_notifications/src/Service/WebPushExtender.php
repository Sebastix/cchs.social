<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\Service;

use Drupal\Core\Url;
use Drupal\web_push\Service\WebPushSender;

/**
 * Extends Web push contrib module's service.
 */
final class WebPushExtender extends WebPushSender implements WebPushExtenderInterface {

  /**
   * {@inheritdoc}
   */
  public function createNotification(): void {
    // For now, just a placeholder values,
    // seems mandatory to create Notification.
    // Then, in PF channel plugin we re-generate the real ones,
    // based on DANSE notifications.
    $push_data = [
      'content' => [
        'title' => 'Default',
        'body' => 'Default',
        'icon' => '',
        'url' => Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(),
      ],
      'subscriptionIds' => NULL,
    ];

    $push_data['options'] = [
      'TTL' => $this->config->get('ttl'),
      'urgency' => $this->config->get('urgency'),
      'topic' => $this->config->get('topic'),
      'batchSize' => (int) $this->config->get('batchSize'),
    ];

    $this->singletonWebPush();
    $this->prepareSubscriptions($push_data);
  }

}
