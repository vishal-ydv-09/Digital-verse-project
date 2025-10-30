<?php

declare(strict_types=1);

namespace Drupal\events_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Events api routes.
 */
final class EventsApiController extends ControllerBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManagerService;

  /**
   * File URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  private FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * Constructs the controller.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
    $this->entityTypeManagerService = $entityTypeManager;
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('file_url_generator'),
    );
  }

  /**
   * Returns upcoming events as JSON.
   */
  public function list(): Response {
    // $currentTime = (new \DateTime('now', new \DateTimeZone('UTC')))
    // ->format('Y-m-d\TH:i:s');
    $query = $this->entityTypeManagerService
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->condition('type', 'event')
      ->sort('field_event_date_time.value', 'ASC')
      ->range(0, 50);

    $nids = $query->execute();

    if (empty($nids)) {
      return new JsonResponse([], 200);
    }

    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $this->entityTypeManagerService
      ->getStorage('node')
      ->loadMultiple($nids);

    $results = [];
    foreach ($nodes as $node) {
      $results[] = $this->serializeEventNode($node);
    }

    return new JsonResponse($results, 200, [
      'Cache-Control' => 'public, max-age=300',
    ]);
  }

  /**
   * Serializes an event node into an array for JSON output.
   */
  private function serializeEventNode(NodeInterface $node): array {
    $imageUrl = NULL;
    if (!$node->get('field_event_image')->isEmpty()) {
      $file = $node->get('field_event_image')->entity;
      if ($file) {
        $imageUrl = $this->fileUrlGenerator
          ->generateAbsoluteString($file->getFileUri());
      }
    }

    $categoryNames = [];
    if (!$node->get('field_category')->isEmpty()) {
      foreach ($node->get('field_category')->referencedEntities() as $term) {
        $categoryNames[] = $term->label();
      }
    }

    $eventDate = NULL;
    if (!$node->get('field_event_date_time')->isEmpty()) {
      $eventDate = $node->get('field_event_date_time')->value;
    }

    return [
      'nid' => (int) $node->id(),
      'title' => $node->label(),
      'field_body' => $node->get('field_body')->value ?? NULL,
      'field_summary' => $node->get('field_summary')->value ?? NULL,
      'field_location' => $node->get('field_location')->value ?? NULL,
      'field_category' => $categoryNames,
      'field_event_date_time' => $eventDate,
      'field_event_image' => $imageUrl,
      'path' => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ];
  }

}
