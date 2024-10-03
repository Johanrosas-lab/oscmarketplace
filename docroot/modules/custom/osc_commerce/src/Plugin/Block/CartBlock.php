<?php
/**
 * @file
 * @author Ricardo Gonzalez
 * Contains \Drupal\cart\Controller\CartBlock.
 */
namespace Drupal\osc_commerce\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Provides a block with 2 URLs in text format.
 *
 * @Block(
 *   id = "cart_block",
 *   admin_label = @Translation("External access by product"),
 * )
 */
class CartBlock extends BlockBase {
  /**
  * {@inheritdoc}
  */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    return $form;
  }

  /**
  * {@inheritdoc}
  */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['my_block_settings'] = $form_state->getValue('my_block_settings');
  }

  /**
   * {@inheritdoc}
   */
   public function build() {
     $current_path = \Drupal::service('path.current')->getPath();
     $productId = substr($current_path, 9);
     $host = \Drupal::request()->getSchemeAndHttpHost();
     $buynow = t("@host/products/buy-now/@productId", array('@host' => $host, '@productId' => $productId));
     $addToCart = t("@host/products/add-to-cart/@productId", array('@host' => $host, '@productId' => $productId));
     return ['#markup' => t('Buy it now:@buy_now</br>Add to Cart:@addToCart',
     array('@buy_now' => $buynow, '@addToCart' => $addToCart))];
    }
}
