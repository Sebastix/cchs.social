<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\Service;

/**
 * WebPushExtender interface definition..
 */
interface WebPushExtenderInterface {

  /**
   * Generate WebPush notifications.
   */
  public function createNotification(): void;

}
