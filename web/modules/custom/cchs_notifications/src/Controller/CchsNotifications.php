<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\Controller;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\DataCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\QueueFactory;
use Drupal\danse_content\Service as DanseContent;
use Drupal\push_framework\ChannelPluginManager;
use Drupal\push_framework\SourceItem;
use Drupal\push_framework\SourcePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for cchs_notifications routes.
 */
final class CchsNotifications extends ControllerBase {

  /**
   * The controller constructor.
   */
  public function __construct(
    private readonly SourcePluginManager $sourcePlugin,
    private readonly ChannelPluginManager $channelPlugin,
    private readonly DanseContent $danseContentService,
    private readonly QueueFactory $queueFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('push_framework.source.plugin.manager'),
      $container->get('push_framework.channel.plugin.manager'),
      $container->get('danse_content.service'),
      $container->get('queue')
    );
  }

  /**
   * Builds the response.
   */
  public function fetch() {

    $notifications = [];

    $json_data = [
      'content' => NULL,
      'user' => NULL,
      'entity' => NULL,
      'parent_entity' => NULL,
    ];

    try {

      /**
       * @var \Drupal\danse\Entity\NotificationInterface[] $notifications
       */
      $danse_events_array = $this->entityTypeManager()->getStorage('danse_event')
        ->loadByProperties([
          'uid' => $this->currentUser()->id(),
          'processed' => 0,
          'push' => 1,
        ]);

      if (!empty($danse_events_array)) {

        $notification_plugin = $this->sourcePlugin->createInstance('danse_notification');
        $channel_plugin = $this->channelPlugin->createInstance('cchs_notifications');
        $plugin = $this->danseContentService->getPlugin();
        $plugin->createNotifications();
        // $worker = $this->queueWorkerManager->createInstance('web_push');
        $queue_factory = $this->queueFactory->get('web_push');
        foreach ($danse_events_array as $danse_event) {

          $notifications_array = $this->entityTypeManager()->getStorage('danse_notification')
            ->loadByProperties([
              'delivered' => 0,
              'seen' => 0,
              'redundant' => 0,
              'event' => $danse_event->id(),
            ]);

          if (!empty($notifications_array)) {
            /** @var \Drupal\danse\Entity\NotificationInterface $notification */
            foreach ($notifications_array as $notification) {
              if ($this->currentUser()->id() != (int) $notification->uid()) {
                $notifications[$notification->id()] = $notification->toArray();
                $entity = $notification_plugin->getObjectAsEntity($notification->id());
                $user_storage = $this->entityTypeManager()->getStorage('user');
                $user = $user_storage->load($notification->uid());
                $json_data['user'] = $user->getDisplayName();
                $content = $channel_plugin->prepareContent($user, $entity, $notification_plugin, $notification->id());
                $langcode = $entity->language()->getId();
                $json_data['content'] = $content[$langcode] ?? $content;
                $json_data['entity'] = [
                  // 'title' => $parent_entity->label(),
                  'bundle_label' => $entity->bundle(),
                  'data' => $entity->toArray(),
                ];
                // $parent_comment = $entity->getParentComment();
                $parent_entity = $entity->getCommentedEntity();
                $json_data['parent_entity'] = [
                  'title' => $parent_entity->label(),
                  'bundle_label' => $parent_entity->bundle(),
                  'data' => $parent_entity->toArray(),
                ];

                $url_options = [
                  'fragment' => 'comment-' . $entity->id(),
                  'absolute' => TRUE,
                  'target' => 'blank_',
                ];

                // $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])
                // ->toString();
                $url = $parent_entity->toUrl(NULL, $url_options)->toString();
                $pushData = [
                  'content' => [
                    'title' => $parent_entity->label(),
                    'body' => $json_data['content']['body'],
                    'icon' => '/themes/custom/hix/favicon.png',
                    // 'image' => '/themes/custom/hix/favicon.png',
                    'url' => $url,
                  ],
                  'options' => [
                    'urgency' => '',
                  ],
                ];

                // TODO: Create DANSE notification action here ?
                // $item = new SourceItem($notification_plugin, $notification->id(), $notification->uid());

                $queue_factory->createItem($pushData);
                // $worker->processItem($pushData);
              }
            }
          }
        }
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      // @todo Log this exception.
    }

    $response = new AjaxResponse();
    $response->addAttachments([
      'library' => [
        'cchs_notifications/notify',
      ],
    ]);

    // $settings['cchs_notifications'][$id] = []];
    // $response->addAttachments([
    // 'drupalSettings' => $settings,
    // ]);
    $response->addCommand(new DataCommand('', 'cchs_notifications', Json::encode($json_data)));
    return $response;
  }

}
