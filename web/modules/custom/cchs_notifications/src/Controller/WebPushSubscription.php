<?php

declare(strict_types=1);

namespace Drupal\cchs_notifications\Controller;

use Drupal\Core\Access\AccessResultAllowed;
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
    'success' => 'Successfully',
    'error' => 'The Notification service is not correctly set. No <em>public key</em> set in config.',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ?DanseService $service,
    ?Service $content_service,
    ?UserDataInterface $user_data,
    ?AccountProxyInterface $current_user,
    private readonly ?ConfigFactoryInterface $configFactory,
    private readonly ?MessengerInterface $messenger,
  ) {
    parent::__construct($entity_type_manager, $service, $content_service, $user_data, $current_user);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): WebPushSubscription {
    return new self(
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
    $access = $this->checkAccessSubscribe($entity_type, $entity_id, $key);
    $this->userData->set('danse', $this->currentUser->id(), $key, 1);
    $response = parent::subscribe($entity_type, $entity_id, $key);
    if ($access instanceof AccessResultAllowed) {
      $this->modifyResponse($response);
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function unsubscribe(string $entity_type, string $entity_id, string $key): AjaxResponse {
    $access = $this->checkAccessUnsubscribe($entity_type, $entity_id, $key);
    $response = parent::unsubscribe($entity_type, $entity_id, $key);
    if ($access instanceof AccessResultAllowed) {

      $this->modifyResponse($response, FALSE);

      // Contrib module web_push does not clean up its subscription entities.
      // Sp let's do it here. Note that client site subscription
      // gets removed with a call above.
      // @todo Implement this call.
      $this->removeWebPushSubscription($response);
    }
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
      // @todo Collect more data here to send to JS.
      $data = [
        'subscribe' => $op,
      ];
      $response->addCommand(new DataCommand('', 'cchs_notifications', $data));
      $subscribe_label = $op ? 'subscribed' : 'unsubscribed';
      $this->messenger->addMessage($this->t('@message @op', [
        '@message' => self::MESSAGES['success'],
        '@op' => $subscribe_label,
      ]));
    }
    else {
      $this->messenger->addError($this->t('@message', [
        '@message' => self::MESSAGES['error'],
      ]));
    }
  }

  /**
   * Remove web_push subscription entity here.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   Ajax response object returned from parent, DANSE controller.
   */
  private function removeWebPushSubscription(AjaxResponse &$response): void {}

}
