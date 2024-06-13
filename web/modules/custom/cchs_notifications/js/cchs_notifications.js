/**
 * @file
 * Manage subscription and unsubscription to Push notification
 */
((Drupal, once) => {
  Drupal.behaviors.cchsNotification = {
    attach: (context, settings) => {

      let title = Drupal.t('Successfully subscribed!');
      const options = {
        message: Drupal.t('Perhaps here should be this node info? Or ome custom text.')
      };
      doNotification(context, title, options);

      const url = '/cch_notification/fetch';
      fetchNotifications(context, url);
      //.then((res) => {
        // const message = JSON.parse(res[2].value);
        // if (message && message.user && message.content) {
        // options.message = message.content.body;
        // title = message.parent_entity && message.parent_entity.title ? Drupal.t(message.parent_entity.title) : Drupal.t('@todo');
        // const send = doNotification(context, title, options);
        // send.then((r) => {
        // r();
        // });
        // }
      // });
    }
  };

  async function fetchNotifications(context, url) {
    const query = {};
    const body = {};
    const response = await fetch(
      `${url}?${new URLSearchParams(query)}`,
      {
        method: 'POST',
        headers: {
          'Content-type': 'application/json'
        },
        body: JSON.stringify(body)
      }
    );
    if (response.ok) {
      return await response.text(); //json();
    }
  }

  async function doNotification(context, title, options) {

    const sendButton = document.querySelector('.danse-subscription-operation');
    if (!sendButton) {
      return;
    }
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

      let notification;
      if (registration && 'showNotification' in registration) {
        notification = registration.showNotification(title, payload);
      }
      else {
        notification = new Notification(title, payload);
      }
      // notification.addEventListener("click", (e) => {
      // clients.openWindow(
      // "https://example.blog.com/2015/03/04/something-new.html",
      // );
      // });
    };

    sendButton.addEventListener('click', sendNotification);
    return sendNotification;

  }

})(Drupal, once);