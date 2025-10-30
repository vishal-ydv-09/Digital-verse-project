## Events API (Custom Drupal Module)

A lightweight JSON endpoint to fetch Event nodes and their key fields.

### Installation

- Place this module in `web/modules/custom/events_api` (already done).
- Enable the module and clear caches:

```bash
drush en events_api -y
drush cr
```

### Endpoint

- URL: `/api/events/upcoming`
- Method: `GET`
- Access: Requires users to have Drupal permission `access content`.

### Usage

Basic request:

```bash
curl -s http://your-site-domain/api/events/upcoming | jq
```

The endpoint currently returns up to 50 published nodes of content type
`event`, sorted by `field_event_date_time` ascending.

Note: If you want to filter to future events only, ensure the controller
sets a lower bound on `field_event_date_time` (the line is present and
can be uncommented). Alternatively, this can be turned into a query
parameter (e.g. `?from=2025-01-01T00:00:00`).

### Response Shape

Array of event objects. Example:

```json
[
  {
    "nid": 123,
    "title": "Sample Event",
    "field_body": "<p>Event body HTML or text</p>",
    "field_summary": "Short summary",
    "field_location": "New York, NY",
    "field_category": ["Conference", "Tech"],
    "field_event_date_time": "2025-11-20T09:00:00",
    "field_event_image":
      "https://your-site-domain/sites/default/files/2025-10/image.jpg",
    "path": "https://your-site-domain/node/123"
  }
]
```

### Fields Returned

- `nid`: Node ID
- `title`: Node title
- `field_body`: Event body (raw value)
- `field_summary`: Event summary (raw value)
- `field_location`: Location text
- `field_category`: Array of term names
- `field_event_date_time`: Datetime (raw value)
- `field_event_image`: Absolute URL to the image file (if present)
- `path`: Absolute canonical URL to the node

If any of the fields are empty or missing, they may be `null` (or
omitted, depending on content).

### Caching

- The response includes `Cache-Control: public, max-age=300` headers.
  Adjust in the controller if needed.

### Dependency Injection

The controller uses dependency injection for core services:

- `entity_type.manager` (as `EntityTypeManagerInterface`) to load and
  query nodes.
- `file_url_generator` (as `FileUrlGeneratorInterface`) to build absolute
  URLs for images.

Constructor and factory are implemented on
`\Drupal\events_api\Controller\EventsApiController`, so the container
provides these services. No `\Drupal::service()` lookups are used inside
request handling.

### Modifying Behavior

- Items per page: Change `->range(0, 50)` in the controller.
- Sorting: Change `->sort('field_event_date_time.value', 'ASC')`.
- Future-only filter: Uncomment the condition comparing
  `field_event_date_time` with the current UTC time, or add a request
  parameter and parse it.

### Troubleshooting

- After code changes, run `drush cr`.
- Ensure the content type machine name is `event` and field machine names
  match:  
  `field_body`, `field_summary`, `field_location`, `field_category`,
  `field_event_date_time`, `field_event_image`.
- Verify permissions: user must have `access content`.
