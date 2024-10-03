<?php
/**
 * @file
 * @author Ricardo Gonzalez
 * Contains \Drupal\osc_commerce\Controller\CartController.
 */
namespace Drupal\osc_commerce\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\commerce\commerce_product;
use Drupal\commerce;
use Drupal\commerce_cart;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_cart\CartManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_price\Price;
use Drupal\cart\Controller\Url;

/**
* Controller routines for cart routes.
*/
class CartController extends ControllerBase {
  /**
  * Commerce Cart Manager.
  * @var \Drupal\commerce_cart\CartManagerInterface
  */
  protected $cartManager;

  /**
  * Commerce Cart Provider.
  * @var \Drupal\commerce_cart\CartProviderInterface
  */
  protected $cartProvider;

  /**
  * Constructs a new CartController object.
  * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
  */
  public function __construct(CartManagerInterface $cart_manager, CartProviderInterface $cart_provider) {
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
  }

  /**
  * Creates a the CartController Container.
  * @param \Drupal\commerce_cart\CartProviderInterface $cart_manager
  * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
  */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'));
  }

  /**
  * Gets the productId to add it to the cart and redirects to the cart page.
  * @param \Drupal\commerce_product\Entity\Product - Id of the product
  * @param \Drupal\commerce_product\Entity\ProductVariation - Id of the product variation
  * @return $this - Redirects to commerce cart page.
  */

  public function buy_now($productId) {
    $destination = \Drupal::service('path.current')->getPath();
    $productObj = Product::load($productId);
    $product_variation_id = $productObj->get('variations')->getValue()[0]['target_id'];
    $storeId = $productObj->get('stores')->getValue()[0]['target_id'];
    $variationobj = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($product_variation_id);
    $store = \Drupal::entityTypeManager()->getStorage('commerce_store')->load($storeId);
    $cart = $this->cartProvider->getCart('default', $store);
    /**
    * Creates a cart, if there's none
    */
    if (!$cart) {
      $cart = $this->cartProvider->createCart('default', $store);
    }
    $line_item_type_storage = \Drupal::entityTypeManager()->getStorage('commerce_order_item_type');
    $cart_manager = \Drupal::service('commerce_cart.cart_manager');
    $line_item = $cart_manager->addEntity($cart, $variationobj);
    return $this->redirect('commerce_cart.page');
  }

  /**
  * Gets the productId to add it to the cart and redirects to cart page
  * @param \Drupal\commerce_product\Entity\Product - Id of the product
  * @param \Drupal\commerce_product\Entity\ProductVariation - Id of the product variation
  * @return $this - Redirects to commerce cart page.
  * TODO: Change redirect - awaiting ticket
  */
  public function add_to_cart($productId) {
    $destination = \Drupal::service('path.current')->getPath();
    $productObj = Product::load($productId);
    $product_variation_id = $productObj->get('variations')->getValue()[0]['target_id'];
    $storeId = $productObj->get('stores')->getValue()[0]['target_id'];
    $variationobj = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($product_variation_id);
    $store = \Drupal::entityTypeManager()->getStorage('commerce_store')->load($storeId);
    $cart = $this->cartProvider->getCart('default', $store);
    /**
    * Creates a cart, if there's none
    */
    if (!$cart) {
      $cart = $this->cartProvider->createCart('default', $store);
    }
    $line_item_type_storage = \Drupal::entityTypeManager()->getStorage('commerce_order_item_type');
    $cart_manager = \Drupal::service('commerce_cart.cart_manager');
    $line_item = $cart_manager->addEntity($cart, $variationobj);
    return $this->redirect('commerce_cart.page');
  }
}
