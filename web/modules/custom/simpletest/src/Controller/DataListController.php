<?php

namespace Drupal\simpletest\Controller;

use Drupal\common_test\Render\MainContent\JsonRenderer;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;  
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

class DataListController extends ControllerBase
{

  public function list(Request $request)
  {
    // Get the search value from the URL (?search=...)
    $search = trim($request->query->get('search', ''));

    // Build database query.
    $query = Database::getConnection()
      ->select('custom_form_submissions', 'c')
      ->fields('c');

    // ✅ Filter by email only.
    if (!empty($search)) {
      $query->condition('email', '%' . $search . '%', 'LIKE');
    }

    $results = $query->execute()->fetchAll();

    // Prepare table rows.
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

    // Table headers.
    $header = ['ID', 'Name', 'Email', 'Phone', 'Course', 'Passport', 'Submitted'];

    // ✅ Add simple HTML search form that actually works.
    $build['search_form'] = [
      '#markup' => '
        <form method="get" action="' . Url::fromRoute('<current>')->toString() . '" style="margin-bottom:15px;">
          <input type="text" name="search" value="' . htmlspecialchars($search) . '" placeholder="Search by Email" style="padding:6px;width:250px;">
          <input type="submit" value="Search" style="padding:6px;">
        </form>
      ',
    ];

    // Build the results table.
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No results found.'),
    ];

    return $build;
  }


  public function dataApi()
  {
    $client = \Drupal::httpClient();
    $response = $client->get('https://fake-json-api.mock.beeceptor.com/companies', [
      'headers' => [
        'Accept' => 'application/json',
      ],
    ]);


    // dd($response->getBody());
    if ($response->getStatusCode() == 200) {
      $data = json_decode($response->getBody(), true);
      //return JsonResponseRenderer::render($data);
       return new JsonResponse($data);
    } else {
      return [
        '#markup' => $this->t('Failed to fetch data from API.'),
      ];
    }

  }


}
