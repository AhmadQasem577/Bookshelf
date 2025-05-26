# Authentication API Documentation

## Base Configuration

**Base URL:** `https://loa/api/auth.php`

All requests are sent to `auth.php` with a query parameter `action` to specify the operation type.

### Required Headers

```http
Content-Type: application/json
Access-Control-Allow-Credentials: true
Access-Control-Allow-Origin: https://localhost:5173
```

---

## API Endpoints

### 1. User Login

**Endpoint:** `POST /api/auth.php?action=login`

**Request Body:**

```json
{
  "email": "user@example.com",
  "password": "yourpassword"
}
```

**Responses:**

**Success (200):**

```json
{
  "status": "success",
  "message": "Logged in",
  "user": {
    "name": "User Name",
    "email": "user@example.com",
    "createdAt": "2025-05-26 12:00:00"
  }
}
```

**Error (400) - Missing Fields:**

```json
{
  "error": "Email and password required"
}
```

**Error (401) - Invalid Credentials:**

```json
{
  "error": "Invalid credentials"
}
```

---

### 2. User Registration

**Endpoint:** `POST /api/auth.php?action=signup`

**Request Body:**

```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "yourpassword"
}
```

**Responses:**

**Success (200):**

```json
{
  "status": "success",
  "message": "User registered"
}
```

**Error (400) - Missing Fields:**

```json
{
  "error": "Name, email, and password required"
}
```

**Error (409) - User Already Exists:**

```json
{
  "error": "User already exists or signup failed"
}
```

---

### 3. User Logout

**Endpoint:** `GET /api/auth.php?action=logout`

**Response:**

**Success (200):**

```json
{
  "status": "success",
  "message": "Logged out"
}
```

---

## CORS Configuration

The API supports Cross-Origin Resource Sharing (CORS) with the following configuration:

- **Allowed Origin:** `https://localhost:5173`
- **Allowed Methods:** `POST`, `GET`, `OPTIONS`
- **Allowed Headers:** `Content-Type`, `Authorization`
- **Credentials:** Supported
- **Preflight Requests:** Handled with `204 No Content` response

---

## Session Management

The API uses PHP sessions for user authentication:

- **Session Storage:** User email and name are stored in the session after successful login
- **Session Variables:**
  - `user_email`: User's email address
  - `user_name`: User's display name
- **Logout:** Clears all session data

---

## Important Notes

- Always send JSON payloads in POST requests
- Include cookies in requests for session authentication
- Use HTTPS in production environments
- Ensure proper error handling for all response codes
