<?php

namespace Drupal\buy_now\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Thank You page after a product purchase.
 */
class BuyProductController extends ControllerBase {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new BuyProductController object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator service.
   */
  public function __construct(AccountProxyInterface $current_user, FileUrlGeneratorInterface $file_url_generator) {
    $this->currentUser = $current_user;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('file_url_generator')
    );
  }

  /**
   * Displays the thank you page.
   *
   * @param mixed $node
   *   The node ID of the product.
   *
   * @return array
   *   A render array for the thank you page.
   */
  public function thankYouPage($node) {
    $product = \Drupal\node\Entity\Node::load($node);

    $user_name = $this->currentUser->getDisplayName();

    $image_urls = [];
    if ($product->hasField('field_images') && !$product->get('field_images')->isEmpty()) {
      foreach ($product->get('field_images') as $image_field) {
        $image_file = File::load($image_field->target_id);
        if ($image_file) {
          $image_urls[] = $this->fileUrlGenerator->generateAbsoluteString($image_file->getFileUri());
        }
      }
    }

    $output_items = [
      $this->t('Thank you Username: @user for purchasing: @product', ['@user' => $user_name, '@product' => $product->label()]),
      $this->t('Quantity: 1'), 
    ];

    if (!empty($image_urls)) {
      foreach ($image_urls as $image_url) {
        $output_items[] = $this->t('Product image: <img src="@image" alt="Product Image">', ['@image' => $image_url]);
      }
    } else {
      $output_items[] = $this->t('No images available.');
    }

    return [
      '#theme' => 'item_list',
      '#items' => $output_items,
      '#allowed_tags' => ['img'],
    ];
  }

}
