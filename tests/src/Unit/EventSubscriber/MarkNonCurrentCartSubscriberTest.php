<?php

namespace Drupal\Tests\commerce_cart_advanced\Unit\EventSubscriber;

use Drupal\commerce_cart_advanced\Event\CartsSplitEvent;
use Drupal\commerce_cart_advanced\EventSubscriber\MarkedNonCurrentCartSubscriber;
use Drupal\commerce_order\Entity\OrderInterface;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem;
use Drupal\Tests\UnitTestCase;

/**
 * Class MarkNonCurrentCartSubscriberTest.
 *
 * Tests the MarkedNonCurrentCartSubscriber functions.
 *
 * @coversDefaultClass \Drupal\commerce_cart_advanced\EventSubscriber\MarkedNonCurrentCartSubscriber
 * @group commerce_cart_advanced
 * @package Drupal\Tests\commerce_cart_advanced\Unit
 */
class MarkNonCurrentCartSubscriberTest extends UnitTestCase {

  /**
   * Tests the checkMarkedNonCurrent() function.
   *
   * @covers ::checkMarkedNonCurrent
   */
  public function testCheckMarkedNonCurrent() {
    // Mock carts.
    // Cart 1.
    // Mock the non_current_field.
    /** @var \Drupal\Core\Field\FieldItemList $non_current_field */
    $non_current_field = $this->prophesize(FieldItemListInterface::class);
    $non_current_field->isEmpty()->willReturn(TRUE);
    $non_current_field->first()->willReturn(NULL);
    $non_current_field = $non_current_field->reveal();
    $cart = $this->prophesize(OrderInterface::class);
    $cart->id()->willReturn(1);
    $cart->get("COMMERCE_CART_ADVANCED_NON_CURRENT_FIELD_NAME")->willReturn($non_current_field);
    $cart = $cart->reveal();

    // Cart 2.
    // Mock the non_current_field.
    /** @var \Drupal\Core\Field\FieldItemList $non_current_field */
    $non_current_field2 = $this->prophesize(FieldItemListInterface::class);
    $non_current_field2->isEmpty()->willReturn(FALSE);
    $non_current_field2->first()->willReturn([]);
    $non_current_field2 = $non_current_field2->reveal();
    $cart2 = $this->prophesize(OrderInterface::class);
    $cart2->id()->willReturn(2);
    $cart2->get("COMMERCE_CART_ADVANCED_NON_CURRENT_FIELD_NAME")->willReturn($non_current_field2);
    $cart2 = $cart2->reveal();

    // Cart 3.
    // Mock the non_current_field.
    /** @var \Drupal\Core\Field\FieldItemList $non_current_field */
    $non_current_field3 = $this->prophesize(FieldItemListInterface::class);
    $non_current_field3->isEmpty()->willReturn(FALSE);
    $non_current_field3->first()->willReturn([]);
    $non_current_field3 = $non_current_field3->reveal();
    $cart3 = $this->prophesize(OrderInterface::class);
    $cart3->id()->willReturn(3);
    $cart3->get("COMMERCE_CART_ADVANCED_NON_CURRENT_FIELD_NAME")->willReturn($non_current_field3);
    $cart3 = $cart3->reveal();

    // Test with NO non_current carts.
    // The CartSplitEvent mock class.
    $current_carts = [1 => $cart, 2 => $cart2];
    $non_current_carts = [3 => $cart3];
    /** @var \Drupal\commerce_cart_advanced\Event\CartsSplitEvent $cart_split_event */
    $cart_split_event = $this->prophesize(CartsSplitEvent::class);
    $cart_split_event->getCurrentCarts()->willReturn($current_carts);
    $cart_split_event->getNonCurrentCarts()->willReturn($non_current_carts);
    $cart_split_event = $cart_split_event->reveal();

    $subscriber = new MarkedNonCurrentCartSubscriber();
    $subscriber->checkMarkedNonCurrent($cart_split_event);

    $this->assertNotNull($cart_split_event);

    // Test with non_current carts.
    // Cart 4.
    // Mock the non_current_field.
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem $first */
    $first = $this->prophesize(BooleanItem::class);
    $first->get('value')->willReturn([
      'value' => 'test',
    ]);
    $first = $first->reveal();
    /** @var \Drupal\Core\Field\FieldItemList $non_current_field */
    $non_current_field4 = $this->prophesize(FieldItemListInterface::class);
    $non_current_field4->isEmpty()->willReturn(FALSE);
    $non_current_field4->first()->willReturn($first);
    $non_current_field4 = $non_current_field4->reveal();
    $cart4 = $this->prophesize(OrderInterface::class);
    $cart4->id()->willReturn(4);
    $cart4->get("COMMERCE_CART_ADVANCED_NON_CURRENT_FIELD_NAME")->willReturn($non_current_field4);
    $cart4 = $cart4->reveal();

    // The CartSplitEvent mock class.
    $current_carts = [1 => $cart, 4 => $cart4];
    $non_current_carts = [3 => $cart3];
    /** @var \Drupal\commerce_cart_advanced\Event\CartsSplitEvent $cart_split_event2 */
    $cart_split_event2 = $this->prophesize(CartsSplitEvent::class);
    $cart_split_event2->getCurrentCarts()->willReturn($current_carts);
    $cart_split_event2->getNonCurrentCarts()->willReturn($non_current_carts);
    // We'll have to call the setCurrentCarts and setNonCurrentCarts() because
    // if this test works as expected, the function will notice that there are
    // carts flagged as "non_current" and move them to the non_current array.
    $cart_split_event2->setCurrentCarts()->willReturn([1 => $cart]);
    $cart_split_event2->setNonCurrentCarts()->willReturn([3 => $cart3, 4 => $cart4]);
    $cart_split_event2 = $cart_split_event2->reveal();

    $subscriber->checkMarkedNonCurrent($cart_split_event2);

    $this->assertNotNull($cart_split_event);
  }

}

