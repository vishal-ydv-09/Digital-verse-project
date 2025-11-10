<?php

namespace Drupal\custom_form_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;

class DataListController extends ControllerBase {

  public function list(Request $request) {
    $search = trim($request->query->get('search', ''));
    $query = Database::getConnection()->select('custom_form_submissions', 'c')->fields('c');
    if ($search) {
      $or = $query->orConditionGroup()
        ->condition('full_name', '%' . $search . '%', 'LIKE')
        ->condition('course', '%' . $search . '%', 'LIKE')
        ->condition('passport', '%' . $search . '%', 'LIKE');
      $query->condition($or);
    }
    $results = $query->execute()->fetchAll();

    $rows = [];
    foreach ($results as $row) {
      $rows[] = [
        'data' => [
          $row->id,
          $row->full_name,
          $row->email,
          $row->phone,
          $row->course,
          $row->passport,
          date('Y-m-d H:i', $row->created),
        ],
      ];
    }

    $header = ['ID', 'Name', 'Email', 'Phone', 'Course', 'Passport', 'Submitted'];
    // $build['search_form'] = [
    //   '#type' => 'search',
    //   '#title' => $this->t('Search'),
    //   '#attributes' => ['placeholder' => $this->t('Search by name, course, passport')],
    // ];
    $build['table'] = ['#type' => 'table', '#header' => $header, '#rows' => $rows];
    return $build;
  }
}
