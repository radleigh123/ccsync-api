# CCSync API

## Introduction

The **CCSync API** is organized around [REST](https://en.wikipedia.org/wiki/REST). The API has predictable resource-oriented URLs, accepts form-encoded request bodies, returns JSON-encoded responses, and uses standard HTTP response codes and authentication. Authentication and authorization are handled securely through **Firebase**.

This API powers the backend of the CCSync's system, supporting functionalities such as user management, events, member tracking, and requirement submissions.

## Getting Started

(If you’re reading this, you’re likely setting up the project for local development.)
This section guides you through installing dependencies, configuring your environment, and running the project locally

**Prerequisites**:
- [Git](https://git-scm.com/install/window)
- PHP 8.4.14+
- Composer 2.8.12+
- [XAMPP](https://www.apachefriends.org/index.htm) (For *MySQL*)

**Optional**:
- API Testing Tools: [Postman](https://www.postman.com/) | [Insomnia](https://insomnia.rest/)
- DB Clients: [DataGrip](https://www.jetbrains.com/datagrip/) | [DBeaver](https://dbeaver.io/) | XAMPP's built-in phpMyAdmin
- Git Clients: [Github Desktop](https://github.com/apps/desktop) | [GitKraken](https://www.gitkraken.com/) | [Fork](https://git-fork.com/)

#### 1) Set up the project

**Clone and Install**
Clone the repository and install the required dependencies
```bash
git clone https://github.com/radleigh123/ccsync-api.git
cd ccsync-api
composer install
```

After installation, create your environment configuration file:
```bash
cp .env.example .env
```

Then open `.env` and configure the following key values:
```
APP_URL=http://localhost:8000
FIREBASE_CREDENTIALS="path/to/firebase-service-account.json"
```

> 📒 **Note**: Ensure your Firebase credentials file exists and the path is correct relative to your project directory.

Then run this Artisan command to generate the application key:
```bash
php artisan key:generate
```

#### 2) Firebase Setup
The CCSync API integrates with **Firebase Authentication** to manage user accounts and secure API access.
You’ll need a Firebase project with a generated **Service Account Key (JSON)**.
Steps:
1. - Go to [Firebase Console](https://console.firebase.google.com/).
2. Select CCSync project.
3. Navigate to **Project Settings → Service Accounts**.
4. Click **Generate New Private Key** and download the JSON file.
5. Place the file in a safe location inside your project (e.g., `/config/firebase/credentials.json`).
6. Update your `.env`:
   ```
   FIREBASE_CREDENTIALS="path/to/firebase-service-account.json"
   ```

> ❗ **IMPORTANT:** Make sure port `8000` is not already in use. If it is, you can specify another port when running the server (e.g., `php artisan serve --port=8080`).

#### 3) Project Migrations

Set up your MySQL database and run the following Laravel commands. Run these Laravel setup commands:
```bash
php artisan migrate:fresh --seed  # Create database schema with sample data
php artisan storage:link          # Link public storage for file access
php artisan config:cache          # Cache configuration for performance
```

#### 4) Running the Server

Start the laravel development server:
```bash
php artisan serve
```
The API should now be accessible at:
```
http://localhost:8000/api
```
You can test it via CLI / Postman / Insomnia:
**CLI**
```bash
curl -X GET http://localhost:8000/api
```

```bash
curl -X GET http://localhost:8000/api/ping
```

## Troubleshooting

**Composer Issues**
If you encounter dependency or configuration errors, run:
```bash
composer diagnose
php -v
```
This will help verify your PHP version and identify potential environment issues.

**Common Issues**

| Problem                         | Possible Cause                     | Solution                                      |
| ------------------------------- | ---------------------------------- | --------------------------------------------- |
| `Could not find driver`         | MySQL extension not enabled        | Enable `extension=mysqli` in `php.ini`        |
| `Firebase user creation failed` | Invalid credentials path           | Double-check `FIREBASE_CREDENTIALS` in `.env` |
| 404 routes                      | Incorrect route registration order | Recheck api routes using `php artisan r:l`    | 

## Next Steps

Once your local environment is running, you can explore:
- `routes/api.php` -- all configured API endpoints
- `app/Http/Controllers` -- logic for each resource (JSON resources IN PROGRESS)
- `app/Models` -- database schema definitions
- `database/seeders` -- initial mock data seedings

For more details, refer to the [API Reference](https://github.com/radleigh123/ccsync-api/blob/master/docs/API-REFERENCE.md).
