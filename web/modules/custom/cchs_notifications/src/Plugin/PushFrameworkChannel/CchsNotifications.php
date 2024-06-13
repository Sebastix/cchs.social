<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\Plugin\PushFrameworkChannel;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\push_framework\ChannelBase;
use Drupal\user\UserInterface;

/**
 * Plugin implementation of the push framework channel.
 *
 * @ChannelPlugin(
 *   id = "cchs_notifications",
 *   label = @Translation("CCHS Notifications"),
 *   description = @Translation("Provides push notifications channel plugin.")
 * )
 */
class CchsNotifications extends ChannelBase {

  /**
   * {@inheritdoc}
   */
  public function getConfigName(): string {
    //return 'pf_cchs_notifications.settings';
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function applicable(UserInterface $user): bool {
    return $this->active;
  }

  /**
   * {@inheritdoc}
   */
  public function send(UserInterface $user, ContentEntityInterface $entity, array $content, int $attempt): string {
    // $language = $user->getPreferredLangcode();
    // if (!isset($content[$language])) {
    // $language = array_keys($content)[0];
    // }
    // $result = FALSE;
    // $result ? self::RESULT_STATUS_SUCCESS : self::RESULT_STATUS_FAILED;.
    // NADA - does not do.
    // \Drupal::service('logger.factory')->get('Test-me')
    // ->notice('<pre>' . print_r($content, TRUE) .'</pre>');.
    return self::RESULT_STATUS_SUCCESS;
  }

}
