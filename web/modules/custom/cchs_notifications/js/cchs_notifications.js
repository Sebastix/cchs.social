/**
 * @file
 * Manage subscription and unsubscription for Push notifications.
 */
((Drupal, drupalSettings) => {

  /**
   * Old school safety check.
   * @type {object}
   */
  Drupal.AjaxCommands = Drupal.AjaxCommands || {};

  /**
   * Promise to check WebPush subscribe status.
   */
  const checkSubscribed = () => {
    let alreadySubscribeStatus = false;
    if (!WebPushSubscription.checkIsAvailable) {
      alreadySubscribeStatus = true;
    }
    return navigator.serviceWorker
      .getRegistration(WebPushSubscription.settings.webPush.serviceWorkerUrl)
      .then(registration => {

        if (!registration) {
          alreadySubscribeStatus = true;
        }
        else {
          registration.pushManager.getSubscription()
            .then((subscription) => {
              if (subscription) {
                alreadySubscribeStatus = false;
              }
              else {
                alreadySubscribeStatus = true;
              }
            });
        }
        return alreadySubscribeStatus;
      });
  };

  /**
   * Hook on core ajax DataCommand to register our subscription.
   *
   * @param {Drupal.Ajax} [ajax]
   *   An Ajax object.
   * @param {object} response
   * @param {number} [status]
   *   The HTTP status code.
   */
  if (Drupal.AjaxCommands.prototype.data) {

    Drupal.AjaxCommands.prototype.data = ((ajax, response, status) => {
      if (status !== 'success') {
        console.error(Drupal.t('Something went wrong, check your php/drupal logs for possible further info.'));
        return;
      }
      if (!WebPushSubscription || !response) {
        return;
      }

      // Keep this as an example to listening to web_push's events (subscribe/unsubscribe can be).
      // document.addEventListener('subscribe', (event) => {});

      if (response.command === 'data' && response.value && response.value.subscribe !== 'undefined') {

        WebPushSubscription.settings = drupalSettings;
        WebPushSubscription.registerSW();

        /**
         * Subscribe or un-subscribe - inherits from DANSE subscription.
         */
        checkSubscribed().then((subscribed) => {
          let op = 'subscribed';
          if (response.value.subscribe) {
            WebPushSubscription.subscribe();
          }
          else {
            op = 'unsubscribed';
            WebPushSubscription.unSubscribe();
          }

          const toFrom = op === 'subscribed' ? 'to' : 'from';
          const title = Drupal.t('Successfully !op !toFrom !title', { '!op': op, '!toFrom': toFrom, '!title': response.value.entity_title });
          const receive = op === 'subscribed' ? 'receive' : 'stop receiving';
          const options = {
            message: Drupal.t('You will !receive notifications for updates within this post.', {'!receive' : receive})
          };
          return doNotification(title, options).then((promise) => {
            return promise();
          });
        });
      }
    });
  }

  /**
   * Notification generate async call.
   *
   * @param {string} title
   *   The notification title.
   * @param {Object} options
   *   Options object for Notification API.
   */
  async function doNotification(title, options) {

    // const sendButton = document.querySelector('.danse-subscription-operation');
    const registration = await navigator.serviceWorker.getRegistration();

    const sendNotification = async () => {
      if (Notification.permission === 'granted') {
        showNotification(options.message);
      }
      else {
        if (Notification.permission !== 'denied') {
          const permission = await Notification.requestPermission();
          if (permission === 'granted') {
            showNotification(options.message);
          }
        }
      }
    };

    const showNotification = body => {

      const payload = {
        body
      };

      if (registration && 'showNotification' in registration) {
        registration.showNotification(title, payload);
      }
      else {
        new Notification(title, payload);
      }
    };

    // sendButton.addEventListener('click', sendNotification);
    return sendNotification;

  }

  /* Old code, keeping for a while, for a reference. */

  // const url = '/cch_notification/fetch';
  // fetchNotifications(url);
  // //.then((res) => {
  // // const message = JSON.parse(res[2].value);
  // // if (message && message.user && message.content) {
  // // options.message = message.content.body;
  // // title = message.parent_entity && message.parent_entity.title ? Drupal.t(message.parent_entity.title) : Drupal.t('@todo');
  // // const send = doNotification(title, options);
  // // send.then((r) => {
  // // r();
  // // });
  // // }
  // // });

  // async function fetchNotifications(url) {
  // const query = {'test': 1};
  // const body = {};
  // const response = await fetch(`${url}?${new URLSearchParams(query)}`, {
  // method: 'POST',
  // headers: {
  // 'Content-type': 'application/json'
  // },
  // body: JSON.stringify(body)
  // }
  // );
  // if (response.ok) {
  // return await response.text();
  // }
  // }

})(Drupal, drupalSettings);
