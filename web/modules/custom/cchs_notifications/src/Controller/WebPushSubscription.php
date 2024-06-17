<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\DataCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\danse\Service as DanseService;
use Drupal\danse_content\Controller\Subscription;
use Drupal\danse_content\Service;
use Drupal\user\UserDataInterface;
use Drupal\web_push\Form\VAPIDForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Construct DANSE content controller extend.
 *
 * Adds up on DANSE subscriptions (on/off),
 * in order to save/delete WebPush subscriptions based on it.
 */
final class WebPushSubscription extends Subscription {

  /**
   * Success and error message strings.
   *
   * @var array
   */
  const MESSAGES = [
    'success' => 'Successfully @op',
    'error' => 'The Notification service is not correctly set. No <em>public key</em>',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    DanseService $service,
    Service $content_service,
    UserDataInterface $user_data,
    AccountProxyInterface $current_user,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly MessengerInterface $messenger,
  ) {
    parent::__construct($entity_type_manager, $service, $content_service, $user_data, $current_user);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): Subscription {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('danse.service'),
      $container->get('danse_content.service'),
      $container->get('user.data'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function subscribe(string $entity_type, string $entity_id, string $key): AjaxResponse {
    $this->userData->set('danse', $this->currentUser->id(), $key, 1);
    $response = parent::subscribe($entity_type, $entity_id, $key);
    $this->modifyResponse($response);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function unsubscribe(string $entity_type, string $entity_id, string $key): AjaxResponse {
    $response = parent::unsubscribe($entity_type, $entity_id, $key);
    $this->modifyResponse($response, FALSE);
    return $response;
  }

  /**
   * Add some data and make ready for WebPush subscription entity.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   Ajax response object returned from parent, DANSE controller.
   * @param bool $op
   *   Subscribe or un-subscribe flag.
   */
  private function modifyResponse(AjaxResponse &$response, bool $op = TRUE): void {

    // Get the VAPID Public Key to add it in JS notification subscription.
    $config = $this->configFactory->get(VAPIDForm::$configId);
    $publicKey = $config->get('publicKey');

    // Check if the public key is set.
    if (!empty($publicKey)) {
      $response->addAttachments([
        'library' => [
          'cchs_notifications/notify',
        ],
      ]);

      $settings = [
        'publicKey' => $publicKey,
        'serviceWorkerUrl' => Url::fromRoute('web_push.service_worker')->toString(),
        'subscribeUrl' => Url::fromRoute('rest.web_push_subscription.POST')->toString(),
      ];
      $attachments['drupalSettings']['webPush'] = $settings;
      $response->addAttachments($attachments);

      // A Flag, subscribe or un-subscribe.
      $data = [
        'subscribe' => $op,
      ];
      $response->addCommand(new DataCommand('', 'cchs_notifications', $data));
      $subscribe_label = $op ? 'subscribed' : 'unsubscribed';
      $this->messenger->addMessage($this->t('@message', [
        '@message' => self::MESSAGES['success'] . ' ' . $subscribe_label,
      ]));
    }
    else {
      $this->messenger->addError($this->t('@message', [
        '@message' => self::MESSAGES['error'],
      ]));
    }
  }

}
