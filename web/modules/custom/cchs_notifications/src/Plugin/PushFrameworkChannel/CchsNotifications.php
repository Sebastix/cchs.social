<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\Plugin\PushFrameworkChannel;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\push_framework\ChannelBase;
use Drupal\user\UserInterface;
use Drupal\web_push\Service\WebPushSender;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The web push sender service.
   *
   * @var \Drupal\web_push\Service\WebPushSender
   */
  protected WebPushSender $webPushSender;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->webPushSender = $container->get('web_push.sender');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigName(): string {
    return 'cchs_notifications.settings';
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

    $parent_entity = NULL;
    $url_options = [
      'absolute' => TRUE,
      'target' => 'blank_',
    ];

    $entity_type = $entity->getEntityTypeId();
    switch ($entity_type) {
      case 'comment':
        /** @var \Drupal\comment\Entity\Comment $entity */
        // $parent_comment = $entity->getParentComment();
        $parent_entity = $entity->getCommentedEntity();
        $url_options['fragment'] = 'comment-' . $entity->id();
        break;

    }

    // $language = $user->getPreferredLangcode();
    // if (!isset($content[$language])) {
    // $language = array_keys($content)[0];
    // }
    $langcode = $entity->language()->getId();
    $title = $parent_entity ? $parent_entity->label() : $entity->label();
    $body = isset($content[$langcode]) ? $content[$langcode]['body'] : $content['body'];
    $url = $parent_entity ? $parent_entity->toUrl(NULL, $url_options)->toString() : $entity->toUrl(NULL, $url_options)->toString();
    $icon = '';
    $push_data = [
      'content' => [
        'title' => $title,
        'body' => $body,
        'icon' => $icon,
        'url' => $url,
      ],
      'options' => [
        'urgency' => '',
      ],
      'subscriptionIds' => NULL,
    ];
    try {
      $this->webPushSender->sendNotification($push_data);
      return self::RESULT_STATUS_SUCCESS;
    }
    catch (\Throwable $e) {
      return self::RESULT_STATUS_FAILED . ': ' . $e->getMessage();
    }
  }

}
