# Flarum Trends

A [Flarum](http://flarum.org) extension to serve API for trending discussions.

## Get Started

### Installation

```bash
composer require liplum/trends
```

### Update

```sh
composer update liplum/trends
```

## Usage

This extension provides an API endpoint to retrieve trending discussions based on recent activity.

### Endpoint

* **`GET /api/trends`**

### Query Parameters

* **`recentDays` (integer, optional):** The number of days to consider for recent discussions. Defaults to `7`.
* **`limit` (integer, optional):** The maximum number of discussions to return. Defaults to `10`.
* **`hotSpotHours` (integer, optional):** The number of hours to consider for recent hot spot activity. Discussions with activity within this timeframe will have a higher weight in the ranking. Defaults to `24`.

### Response

The API returns a JSON array of discussion objects. Each discussion object contains the following properties:

* **`id` (integer):** The discussion ID.
* **`title` (string):** The discussion title.
* **`commentCount` (integer):** The number of comments in the discussion.
* **`createdAt` (string):** The creation time of the discussion in ISO 8601 format.
* **`lastActivityAt` (string):** The last activity time of the discussion in ISO 8601 format, using the created time if the last posted time is null.
* **`user` (object):** An object containing the user's ID and username.
  * **`id` (integer):** The user ID.
  * **`username` (string):** The username.

### Example Request

```http
GET /api/trends?recentDays=14&limit=5&hotSpotHours=12
```

### Example Response

```json
[
  {
    "id": 123,
    "title": "Discussion Title 1",
    "commentCount": 50,
    "createdAt": "2023-10-27T10:00:00+00:00",
    "lastActivityAt": "2023-10-27T11:30:00+00:00",
    "user": {
      "id": 1,
      "username": "user1"
    }
  },
  {
    "id": 456,
    "title": "Discussion Title 2",
    "commentCount": 30,
    "createdAt": "2023-10-26T15:30:00+00:00",
    "lastActivityAt": "2023-10-26T15:30:00+00:00",
    "user": {
      "id": 2,
      "username": "user2"
    }
  },
  // ... more discussions
]
```

### Notes

* Hidden, locked, and private discussions are excluded from the results.
* The ranking is based on comment count, with a higher weight given to discussions with recent activity within the specified `hotSpotHours`.
* You can customize the number of days, limit of discussions and hot spot hours using the query parameters.
