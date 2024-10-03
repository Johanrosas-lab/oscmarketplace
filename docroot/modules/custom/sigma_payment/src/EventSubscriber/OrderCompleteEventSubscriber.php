<?php
namespace Drupal\sigma_payment\EventSubscriber;

use Drupal\commerce_order\Event\OrderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_order\Event\OrderEvent;

class OrderCompleteEventSubscriber implements EventSubscriberInterface{

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    // TODO: Implement getSubscribedEvents() method.
    return [OrderEvents::ORDER_PAID => ['onPaid']];
  }

  /**
   * Update the status order after complete the paid.
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   */
  public function onPaid(OrderEvent $event) {
    $order = $event->getOrder();
    // Validate if the paid is complete
    if ($order->getTotalPaid()->equals($order->getTotalPrice())) {
      // Update the state order to completed.
      $order->set('state', 'completed');
      $order->setCompletedTime(\Drupal::time()->getCurrentTime());
    }
  }
}
