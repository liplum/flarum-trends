# Flarum Trends

A [Flarum](http://flarum.org) extension to serve API for trending discussions.

## Get Started

### Installation

To install the dependencies for this extension, run the following commands:

```bash
composer require michaelbelgium/flarum-discussion-views
composer require liplum/flarum-trends
```

### Update

```sh
composer update liplum/flarum-trends
```

## Usage

This extension provides an API endpoint to retrieve trending discussions based on recent activity.

### Endpoint

* **`GET /api/trends`**

### Query Parameters

* **`limit` (integer, optional):** The maximum number of discussions to return. Defaults to `10`.

### Response

The API returns a JSON array of discussion objects. Each discussion object contains the following properties:

* **`id` (string):** The discussion ID.
* **`title` (string):** The discussion title.
* **`commentCount` (integer):** The number of comments in the discussion.
* **`participantCount` (integer):** The number of participants in the discussion.
* **`viewCount` (integer):** The number of views of the discussion.
* **`createdAt` (string):** The creation time of the discussion in ISO 8601 format.
* **`lastActivityAt` (string):** The last activity time of the discussion in ISO 8601 format.
* **`shareUrl` (string):** The URL to share the discussion.
* **`trendingScore` (number):** The trending score of the discussion.
* **`user` (object):** An object containing the user's ID and username.
  * **`id` (string):** The user ID.
  * **`username` (string):** The username.

### Typing

```ts
interface TrendingDiscussionsResponse {
  data: TrendingDiscussion[];
}

interface TrendingDiscussion {
  type: 'discussions';
  id: string;
  attributes: {
    title: string;
    commentCount: number;
    participantCount: number;
    viewCount: number;
    createdAt: string;
    lastActivityAt: string;
    shareUrl: string;
    trendingScore: number;
  };
  relationships: {
    user: {
      data: {
        type: 'users';
        id: string;
        attributes: {
          username: string;
        };
      };
    };
  };
}
```

### Example Request

```http
GET /api/trends/recent?limit=5
```

### Example Response

```json
{
  "data": [
    {
      "type": "discussions",
      "id": "123",
      "attributes": {
        "title": "Discussion Title 1",
        "commentCount": 50,
        "participantCount": 20,
        "viewCount": 1000,
        "createdAt": "2023-10-27T10:00:00+00:00",
        "lastActivityAt": "2023-10-27T11:30:00+00:00",
        "shareUrl": "https://discuss.flarum.org/d/123",
        "trendingScore": 1234.56
      },
      "relationships": {
        "user": {
          "data": {
            "type": "users",
            "id": "1",
            "attributes": {
              "username": "user1"
            }
          }
        }
      }
    },
    {
      "type": "discussions",
      "id": "456",
      "attributes": {
        "title": "Discussion Title 2",
        "commentCount": 30,
        "participantCount": 15,
        "viewCount": 750,
        "createdAt": "2023-10-26T15:30:00+00:00",
        "lastActivityAt": "2023-10-26T15:30:00+00:00",
        "shareUrl": "https://discuss.flarum.org/d/456",
        "trendingScore": 876.54
      },
      "relationships": {
        "user": {
          "data": {
            "type": "users",
            "id": "2",
            "attributes": {
              "username": "user2"
            }
          }
        }
      }
    }
  ]
}
```

### Trending Score Formula

$S$ = ($W_c \times N_c$) + ($W_p \times N_p$) + ($W_v \times N_v$) - $e^{-\lambda \Delta t}$

Where:

* $W_c$: Weight assigned to comment count.
* $N_c$: Number of comments in the discussion.
* $W_p$: Weight assigned to participant count.
* $N_p$: Number of participants in the discussion.
* $W_v$: Weight assigned to view count.
* $N_v$: Number of views of the discussion.
* $\lambda$: Decay factor that controls the rate of time decay.
* $\Delta$: Time difference between the current time and the created_at time.

The trending score is calculated for each discussion, and discussions are then sorted in descending order based on their scores.

### Notes

* Hidden, locked, and private discussions are excluded from the results.
* The ranking is based on comment count, with a higher weight given to discussions with recent activity within the specified `hotSpotHours`.
* You can customize the number of days, limit of discussions and hot spot hours using the query parameters.
