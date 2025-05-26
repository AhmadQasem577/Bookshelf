# Books Retrieval API Documentation

## Base Configuration

**Base URL:**`https://yourdomain.com/api/books.php`

All requests are sent to `books.php` with a query parameter `action` to specify the operation type.

### Required Headers

```http
Content-Type: application/json
Access-Control-Allow-Credentials: true
Access-Control-Allow-Origin: https://localhost:5173
```

---

## API Endpoints

### 1. List All Books

**Endpoint:**`GET /api/books.php?action=listBooks`

**Authentication:** Not required

**Description:** Retrieves a list of all published books in the system.

**Example Request:**

```javascript
fetch('/api/books.php?action=listBooks', {
  method: 'GET',
  credentials: 'include'
});
```

**Response:**

**Success (200):**

```json
{
  "status": "success",
  "books": [
    {
      "title": "Book Title",
      "author": "Author Name",
      "description": "Book description",
      "publisher": "Publisher Name",
      "publishDate": "2025-05-26",
      "postDate": "2025-05-26 12:00:00"
    }
  ]
}
```

---

### 2. Search Books

**Endpoint:**`GET /api/books.php?action=searchBook&title={search_term}`

**Alternative:**`POST /api/books.php?action=searchBook`

**Authentication:** Not required

**Description:** Searches for books by title.

**URL Parameter Method:**

```javascript
const searchTerm = encodeURIComponent('search query');
fetch(`/api/books.php?action=searchBook&title=${searchTerm}`, {
  method: 'GET',
  credentials: 'include'
});
```

**JSON Body Method:**

```javascript
fetch('/api/books.php?action=searchBook', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'search query'
  }),
  credentials: 'include'
});
```

**Responses:**

**Success (200):**

```json
{
  "status": "success",
  "books": [
    {
      "title": "Matching Book Title",
      "author": "Author Name",
      "description": "Book description",
      "publisher": "Publisher Name",
      "publishDate": "2025-05-26",
      "postDate": "2025-05-26 12:00:00"
    }
  ]
}
```

**Error (400) - Missing Search Term:**

```json
{
  "error": "Search title required"
}
```

---

### 3. List Books by User

**Endpoint:**`GET /api/books.php?action=listBooksByUser`

**Authentication:** Required (session-based)

**Description:** Retrieves all books published by the authenticated user.

**Example Request:**

```javascript
fetch('/api/books.php?action=listBooksByUser', {
  method: 'GET',
  credentials: 'include'
});
```

**Responses:**

**Success (200):**

```json
{
  "status": "success",
  "books": [
    {
      "title": "User's Book",
      "author": "User Name",
      "description": "Book description",
      "publisher": "Publisher Name",
      "publishDate": "2025-05-26",
      "postDate": "2025-05-26 12:00:00"
    }
  ]
}
```

**Error (401) - Not Authenticated:**

```json
{
  "error": "Not authenticated"
}
```

---

### 4. List Favorite Books

**Endpoint:**`GET /api/books.php?action=listFavoriteBooks`

**Authentication:** Required (session-based)

**Description:** Retrieves all books marked as favorites by the authenticated user.

**Example Request:**

```javascript
fetch('/api/books.php?action=listFavoriteBooks', {
  method: 'GET',
  credentials: 'include'
});
```

**Responses:**

**Success (200):**

```json
{
  "status": "success",
  "books": [
    {
      "title": "Favorite Book",
      "author": "Author Name",
      "description": "Book description",
      "publisher": "Publisher Name",
      "publishDate": "2025-05-26",
      "postDate": "2025-05-26 12:00:00"
    }
  ]
}
```

**Error (401) - Not Authenticated:**

```json
{
  "error": "Not authenticated"
}
```

---

## Response Data Structure

All successful book retrieval endpoints return an array of book objects with the following structure:

### Book Object Properties


| Field         | Type   | Description                                                                  |
| ------------- | ------ | ---------------------------------------------------------------------------- |
| `title`       | string | The book's title                                                             |
| `author`      | string | The book's author                                                            |
| `description` | string | Brief description of the book                                                |
| `publisher`   | string | Name of the publisher/user who uploaded the book                             |
| `publishDate` | string | Original publication date (YYYY-MM-DD format)                                |
| `postDate`    | string | Date when the book was uploaded to the platform (YYYY-MM-DD HH:MM:SS format) |

---

## CORS Configuration

The API supports Cross-Origin Resource Sharing (CORS) with the following configuration:

* **Allowed Origin:**`https://localhost:5173`
* **Allowed Methods:**`POST`, `GET`, `OPTIONS`
* **Allowed Headers:**`Content-Type`, `Authorization`
* **Credentials:** Supported
* **Preflight Requests:** Handled with `204 No Content` response

---

## Authentication Requirements

### Public Endpoints (No Authentication Required)

* `listBooks` - View all published books
* `searchBook` - Search books by title

### Protected Endpoints (Authentication Required)

* `listBooksByUser` - View user's own published books
* `listFavoriteBooks` - View user's favorite books

Authentication is handled via PHP sessions. Users must be logged in with a valid `user_id` in the session.

---

## Error Handling

All endpoints return appropriate HTTP status codes:

* **200**: Success
* **400**: Bad Request (missing required parameters)
* **401**: Unauthorized (authentication required)

### Common Error Response Format

```json
{
  "error": "Error message description"
}
```

---

## Usage Examples

### Fetch All Books

```javascript
async function getAllBooks() {
  try {
    const response = await fetch('/api/books.php?action=listBooks', {
      credentials: 'include'
    });
    const data = await response.json();
  
    if (data.status === 'success') {
      console.log('Books:', data.books);
    }
  } catch (error) {
    console.error('Error fetching books:', error);
  }
}
```

### Search Books

```javascript
async function searchBooks(searchTerm) {
  try {
    const response = await fetch(`/api/books.php?action=searchBook&title=${encodeURIComponent(searchTerm)}`, {
      credentials: 'include'
    });
    const data = await response.json();
  
    if (data.status === 'success') {
      console.log('Search results:', data.books);
    } else {
      console.error('Search error:', data.error);
    }
  } catch (error) {
    console.error('Error searching books:', error);
  }
}
```

### Get User's Books

```javascript
async function getUserBooks() {
  try {
    const response = await fetch('/api/books.php?action=listBooksByUser', {
      credentials: 'include'
    });
    const data = await response.json();
  
    if (response.status === 401) {
      console.log('User not authenticated');
      return;
    }
  
    if (data.status === 'success') {
      console.log('User books:', data.books);
    }
  } catch (error) {
    console.error('Error fetching user books:', error);
  }
}
```

---

## Important Notes

* **Session Management:** Include cookies in requests for session authentication
* **URL Encoding:** Always encode search terms in URL parameters
* **Empty Results:** Successful responses may contain empty arrays if no books match the criteria
* **Case Sensitivity:** Check with backend implementation for search case sensitivity
* **Rate Limiting:** Consider implementing rate limiting for search endpoints in production
* **Security:** Use HTTPS in production environments
