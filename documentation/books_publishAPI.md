# Books Management API Documentation

## Base Configuration

**Base URL:**`https://yourdomain.com/api/books_publish.php`

All requests are sent to `books_publish.php` with a query parameter `action` to specify the operation type.

### Required Headers

```http
Content-Type: multipart/form-data (for file uploads)
Access-Control-Allow-Credentials: true
Access-Control-Allow-Origin: https://localhost:5173
```

### Authentication

All endpoints require user authentication via PHP sessions. Users must be logged in with a valid `user_id` in the session.

---

## API Endpoints

### 1. Create Book

**Endpoint:**`POST /api/books_publish.php?action=createBook`

**Content-Type:**`multipart/form-data`

**Required Form Fields:**

* `title` (string): Book title
* `author` (string): Book author
* `description` (string): Book description
* `publish_date` (string): Publication date
* `pdf` (file): PDF file of the book

**Optional Form Fields:**

* `image` (file): Book cover image

**Example Request:**

```javascript
const formData = new FormData();
formData.append('title', 'My Book Title');
formData.append('author', 'Author Name');
formData.append('description', 'Book description');
formData.append('publish_date', '2025-05-26');
formData.append('pdf', pdfFile);
formData.append('image', imageFile); // optional

fetch('/api/books_publish.php?action=createBook', {
  method: 'POST',
  body: formData,
  credentials: 'include'
});
```

**Responses:**

**Success (200):**

```json
{
  "status": "success",
  "message": "Book created"
}
```

**Error (400) - Missing Fields:**

```json
{
  "error": "Missing field: title"
}
```

**Error (400) - Missing PDF:**

```json
{
  "error": "PDF file is required and must upload successfully"
}
```

**Error (401) - Not Authenticated:**

```json
{
  "error": "Not authenticated"
}
```

**Error (500) - Creation Failed:**

```json
{
  "error": "Failed to create book"
}
```

---

### 2. Edit Book

**Endpoint:**`POST /api/books_publish.php?action=editBook&book_id={id}`

**Content-Type:**`multipart/form-data`

**Required Parameters:**

* `book_id` (URL parameter or form field): ID of the book to edit

**Optional Form Fields:**

* `title` (string): Updated book title
* `author` (string): Updated book author
* `description` (string): Updated book description
* `publish_date` (string): Updated publication date
* `pdf` (file): New PDF file (optional)
* `image` (file): New cover image (optional)

**Example Request:**

```javascript
const formData = new FormData();
formData.append('title', 'Updated Title');
formData.append('author', 'Updated Author');
// Only include fields you want to update

fetch('/api/books_publish.php?action=editBook&book_id=123', {
  method: 'POST',
  body: formData,
  credentials: 'include'
});
```

**Responses:**

**Success (200):**

```json
{
  "status": "success",
  "message": "Book updated successfully"
}
```

**Error (400) - Missing Book ID:**

```json
{
  "error": "Missing book_id"
}
```

**Error (401) - Not Authenticated:**

```json
{
  "error": "Not authenticated"
}
```

**Error (403) - Update Failed:**

```json
{
  "error": "Failed to update book. Check ownership or input"
}
```

---

### 3. Delete Book

**Endpoint:**`POST /api/books_publish.php?action=deleteBook&book_id={id}`

**Required Parameters:**

* `book_id` (URL parameter or JSON body): ID of the book to delete

**Example Request (URL Parameter):**

```javascript
fetch('/api/books_publish.php?action=deleteBook&book_id=123', {
  method: 'POST',
  credentials: 'include'
});
```

**Example Request (JSON Body):**

```javascript
fetch('/api/books_publish.php?action=deleteBook', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    book_id: 123
  }),
  credentials: 'include'
});
```

**Responses:**

**Success (200):**

```json
{
  "status": "success",
  "message": "Book deleted"
}
```

**Error (400) - Missing Book ID:**

```json
{
  "error": "Missing book_id"
}
```

**Error (401) - Not Authenticated:**

```json
{
  "error": "Not authenticated"
}
```

**Error (403) - Deletion Failed:**

```json
{
  "error": "You do not have permission to delete this book or it does not exist"
}
```

---

## CORS Configuration

The API supports Cross-Origin Resource Sharing (CORS) with the following configuration:

* **Allowed Origin:**`https://localhost:5173`
* **Allowed Methods:**`POST`, `GET`, `OPTIONS`
* **Allowed Headers:**`Content-Type`, `Authorization`
* **Credentials:** Supported
* **Preflight Requests:** Handled with `204 No Content` response

---

## File Upload Requirements

### PDF Files

* **Required for:** Create Book
* **Optional for:** Edit Book
* **Validation:** File must upload successfully (UPLOAD\_ERR\_OK)
* **Storage:** File content is read and stored in database

### Image Files

* **Required for:** None (always optional)
* **Optional for:** Create Book, Edit Book
* **Validation:** If provided, file must upload successfully
* **Storage:** File content is read and stored in database

---

## Error Handling

All endpoints return appropriate HTTP status codes:

* **200**: Success
* **400**: Bad Request (missing required fields/parameters)
* **401**: Unauthorized (not authenticated)
* **403**: Forbidden (insufficient permissions)
* **500**: Internal Server Error

---

## Important Notes

* **Authentication Required:** All endpoints require valid session authentication
* **File Uploads:** Use `multipart/form-data` for requests with file uploads
* **Book Ownership:** Users can only edit/delete books they created
* **Session Management:** Include cookies in requests for session authentication
* **File Size Limits:** Check server configuration for maximum file upload sizes
* **Security:** Use HTTPS in production environments
