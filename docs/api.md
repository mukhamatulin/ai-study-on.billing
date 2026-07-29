# StudyOn.Billing API

## Auth

### Register

`POST /api/v1/register`

```json
{
  "email": "student@example.com",
  "password": "password"
}
```

### Login

`POST /api/v1/auth`

```json
{
  "email": "student@example.com",
  "password": "password"
}
```

Both methods return:

```json
{
  "token": "...",
  "email": "student@example.com",
  "roles": ["ROLE_USER"],
  "balance": "1000.00"
}
```

### Current User

`GET /api/v1/users/current`

Requires bearer token and returns current email, roles and balance.

## Courses

`GET /api/v1/courses`

`GET /api/v1/courses/{code}`

`POST /api/v1/courses/{code}/pay`

Payment requires:

```http
Authorization: Bearer {token}
```

## Transactions

`GET /api/v1/transactions`

Requires bearer token.

Filters:

- `filter[type]=payment|deposit`
- `filter[course_code]=course-code`
- `filter[skip_expired]=1`

## Admin Course Management

Requires a token for a user with `ROLE_SUPER_ADMIN`.

`POST /api/v1/courses`

`POST /api/v1/courses/{currentCode}`

```json
{
  "type": "rent",
  "title": "Doctrine ORM на практике",
  "code": "doctrine-practice",
  "price": "99.90"
}
```

## Commands

```bash
php bin/console payment:ending:notification
php bin/console payment:report
```
