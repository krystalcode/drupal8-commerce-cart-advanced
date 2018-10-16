<?php

namespace Drupal\Tests\commerce_cart_advanced\Unit\EventSubscriber;

use Drupal\commerce_cart_advanced\Event\CartsSplitEvent;
use Drupal\commerce_cart_advanced\EventSubscriber\OneCartPerStoreSubscriber;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class OneCartPerStoreSubscriberTest.
 *
 * Tests the OneCartPerStoreSubscriber functions.
 *
 * @coversDefaultClass \Drupal\commerce_cart_advanced\EventSubscriber\OneCartPerStoreSubscriber
 * @group commerce_cart_advanced
 * @package Drupal\Tests\commerce_cart_advanced\Unit
 */
class OneCartPerStoreSubscriberTest extends UnitTestCase {

  /**
   * Tests the oneCartPerStore() function.
   *
   * @covers ::oneCartPerStore
   */
  public function testOneCartPerStore() {
    // Mock carts.
    // Cart 1.
    $cart = $this->prophesize(OrderInterface::class);
    $cart->getStoreId()->willReturn(101);
    $cart->id()->willReturn(1);
    $cart = $cart->reveal();

    // Cart 2.
    $cart2 = $this->prophesize(OrderInterface::class);
    $cart2->getStoreId()->willReturn(101);
    $cart2->id()->willReturn(2);
    $cart2 = $cart2->reveal();

    // Cart 3.
    $cart3 = $this->prophesize(OrderInterface::class);
    $cart3->getStoreId()->willReturn(102);
    $cart3->id()->willReturn(3);
    $cart3 = $cart3->reveal();

    // The CartSplitEvent mock class.
    $carts = [$cart, $cart2, $cart3];
    /** @var \Drupal\commerce_cart_advanced\Event\CartsSplitEvent $cart_split_event */
    $cart_split_event = $this->prophesize(CartsSplitEvent::class);
    $cart_split_event->getCarts()->willReturn($carts);
    $current_carts = [1 => $cart, 3 => $cart3];
    $non_current_carts = [0 => $cart, 2 => $cart2];
    $cart_split_event->setCurrentCarts($current_carts)->willReturn($current_carts);
    $cart_split_event->setNonCurrentCarts(array_diff_key($carts, $current_carts))->willReturn($non_current_carts);
    $cart_split_event = $cart_split_event->reveal();

    $subscriber = new OneCartPerStoreSubscriber();
    $subscriber->oneCartPerStore($cart_split_event);

    $this->assertNotNull($cart_split_event);
  }

}

