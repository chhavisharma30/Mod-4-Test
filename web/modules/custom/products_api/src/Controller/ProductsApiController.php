<?php

namespace Drupal\products_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ProductsApiController class to expose the products details through an API.
 */
class ProductsApiController extends ControllerBase {

  /**
   * Protected variable entityTypeManager to store.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Protected variable entityTypeManager to store.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Construct function to initialize objects of services.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager variable for accessing node.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   FIle Url Generator variable for generating images url.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileUrlGeneratorInterface $fileUrlGenerator) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  /**
   * Create function to create objects of services using container.
   *
   * @param mixed $container
   *   Container to create objects of services.
   *
   * @return static
   */
  public static function create($container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_url_generator')
    );
  }

  /**
   * Function to expose products using API.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return Json response for api.
   */
  public function showProducts() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'product')
      ->condition('status', 1)
      ->accessCheck(FALSE);

    $nids = $query->execute();

    $products = [];

    if (!empty($nids)) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      foreach ($nodes as $node) {
        $products[] = [
          'title' => $node->getTitle(),
          'description' => $node->get('field_description')->value,
          'price' => $node->get('field_price')->value,
          'images' => $this->getImages($node),
        ];
      }
    }

    return new JsonResponse($products);
  }

  /**
   * Function to get link of product images.
   *
   * @param mixed $node
   *   Parameter to access the node.
   *
   * @return string[]
   *   Return an array of image urls as strings.
   */
  protected function getImages($node) {
    $images = [];
    $image_field = $node->get('field_images');

    if (!$image_field->isEmpty()) {
      foreach ($image_field as $image) {
        $file = $this->entityTypeManager->getStorage('file')->load($image->target_id);
        if ($file) {
          $images[] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }

    return $images;
  }

}
