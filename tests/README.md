# ISDN PHPUnit Test Suite

Automated unit tests for the IslandLink Sales Distribution Network (ISDN) PHP MVC application.

## Requirements

- PHP 8.0 or higher
- Composer (for PHPUnit dependency)

## Installation

```bash
composer install
```

## Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Or with PHP
php vendor/bin/phpunit

# Run specific test class
php vendor/bin/phpunit tests/Unit/AuthenticationTest.php

# Run with verbose output
php vendor/bin/phpunit --verbose
```

## Folder Structure

```
tests/
├── bootstrap.php          # PHPUnit bootstrap (loads config, functions)
├── README.md              # This file
└── Unit/
    ├── AuthenticationTest.php    # Password hashing, RBAC, session, tokens
    ├── DeliveryEfficiencyTest.php # Efficiency calculation, classification
    ├── EmailServiceTest.php      # Email templates, token format (no real emails)
    └── SecurityTest.php          # Sanitization, prepared statements
```

## Test Coverage

### 1. Authentication Testing
- Password hashing (bcrypt) and verification
- Invalid password rejection
- Role-based access (dashboard_page_for_role, is_page_allowed_for_role)
- Session login simulation
- Password reset token format (64 char hex)
- Expired token rejection logic

### 2. Delivery Efficiency Logic
- Correct efficiency percentage: `(on_time / completed) * 100`
- Zero-division safety (returns null when completed = 0)
- Performance classification: good (≥80%), medium (50–79%), bad (<50%), empty (null)

### 3. Email Service Logic (no real emails sent)
- Email template generation
- Token format (64-char hex)
- Login notification message formatting
- Browser name detection (Chrome, Firefox, Edge, etc.)

### 4. Security Testing
- Input sanitization (trim, strip_tags, htmlspecialchars)
- Prepared statement usage (mock verification)
- Token unpredictability

## Configuration

- `phpunit.xml` in project root defines bootstrap and test directories
- Tests use `MAIL_METHOD=log` for email tests (writes to temp, no SMTP)
