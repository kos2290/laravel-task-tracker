# Task Tracker API with Laravel

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)
![Docker](https://img.shields.io/badge/Docker-20.10%2B-2496ED?style=for-the-badge&logo=docker)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)
![Pusher](https://img.shields.io/badge/Pusher-Realtime-300D4F?style=for-the-badge&logo=pusher)

A simple yet functional API for a task tracker, developed as a test assignment for a Middle PHP Developer position. This project showcases proficiency in a modern tech stack, including Laravel, Docker, WebSockets, and third-party API integration.

## 📚 Table of Contents

- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Prerequisites](#-prerequisites)
- [Installation and Setup](#-installation-and-setup)
  - [1. Clone the Repository](#1-clone-the-repository)
  - [2. Configure Environment](#2-configure-environment)
  - [3. Start Docker Containers](#3-start-docker-containers)
  - [4. Install Dependencies and Set Up the Application](#4-install-dependencies-and-set-up-the-application)
- [API Usage](#-api-usage)
  - [Postman Collection for API](#postman-collection-for-api)
  - [Testing WebSockets (Real-time)](#testing-websockets-real-time)
- [Running Tests](#-running-tests)

## ✨ Key Features

* **User Authentication:** Registration and login using Laravel Sanctum API tokens.
* **Task Management (CRUD):** Authenticated users can create, view, edit, and delete tasks.
* **Access Control:** Users can only view and manage tasks where they are the creator or the assignee.
* **Real-time Updates:** When a task's status changes, all relevant users are notified via WebSockets (Pusher).
* **Telegram Notifications:** When a new task is created, a bot sends an informational message to a specified Telegram chat.
* **Fully Dockerized:** The project is easily deployed and run using Docker and Docker Compose.
* **Test Coverage:** Core API functionality is covered by Feature tests (PHPUnit).

## 🛠️ Tech Stack

* **Backend:** PHP 8.2, Laravel 12
* **Database:** MySQL 8.0
* **Web Server:** Nginx
* **Containerization:** Docker, Docker Compose
* **Real-time:** Pusher
* **Notifications:** Telegram Bot API
* **Testing:** PHPUnit

## 📋 Prerequisites

To run this project locally, you will need:

* [Docker](https://www.docker.com/get-started)
* [Docker Compose](https://docs.docker.com/compose/install/)
* [Git](https://git-scm.com/)

## 🚀 Installation and Setup

Follow these steps for a quick setup.

### 1. Clone the Repository

```bash
git clone https://github.com/kos2290/laravel-task-tracker
cd laravel-task-tracker
```

### 2. Configure Environment
Create an environment file from the example and open it for editing.

```bash
cp .env.example .env
```

In the .env file, you must fill in the following variables:

### Database Settings

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_task_tracker
DB_USERNAME=user
DB_PASSWORD=secret
```

### Pusher Settings
(Get these keys from your [dashboard.pusher.com](https://dashboard.pusher.com/))

```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=your_pusher_cluster
```

### Telegram Settings
(Get the token from `@BotFather` in Telegram. Find your chat ID using `@userinfobot` or similar bots)

```
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id
```

### 3. Start Docker Containers
This command will build the images and start all required services (app, webserver, db) in the background.

```bash
docker-compose up -d --build
```

### 4. Install Dependencies and Set Up the Application
Now, execute the following commands inside the app container to complete the setup.

```bash
# Install PHP dependencies
docker-compose exec php-cli composer install

# Generate the Laravel application key
docker-compose exec php-cli php artisan key:generate

# Run database migrations and seeders
docker-compose exec php-cli php artisan migrate --seed
```
Alternatively, you can use the Makefile run command.

```bash
make cli
```
After running, enter the following commands in the terminal of the Docker container:

```bash
composer install

php artisan key:generate

php artisan migrate --seed
```

🎉 Done! The application will be available at http://localhost:8080.

## ⚙️ API Usage
All API endpoints are prefixed with `/api`. To interact with the API, you need a `Bearer` token, which can be obtained upon registration (`/api/register`) or login (`/api/login`).

#### Postman Collection for API
For convenient testing of all API endpoints (registration, login, task CRUD), you can import the ready-made Postman collection.
- [Postman API CRUD collection](https://www.postman.com/kos2290/workspace/public-workspace/collection/3620554-58cd7393-956c-4c97-9c21-c4992b19dddd?action=share&creator=3620554)
- [Postman Websocket connection](https://www.postman.com/kos2290/public-workspace/ws-raw-request/6843f71e642b87a2389aa42d)

#### API endpoints
- Registration: `POST http://localhost:8080/api/register`
- Login: `POST http://localhost:8080/api/login`
- Logout: `POST http://localhost:8080/api/logout`
- Get tasks: `GET http://localhost:8080/api/tasks`
- Store task: `POST http://localhost:8080/api/tasks`
- Show task: `GET http://localhost:8080/api/tasks/{id}`
- Update task: `PUT http://localhost:8080/api/tasks/{id}`
- Destroy task: `DELETE http://localhost:8080/api/tasks/{id}`
- Broadcast auth: `POST http://localhost:8080/broadcasting/auth`

#### Testing WebSockets (Real-time)
When a task's status changes, the API dispatches an event. You can subscribe to it to see updates in real-time.

This Postman collection contains a pre-configured WebSocket request to connect to Pusher.

#### How to use:

1. Open the WebSocket request in Postman.
2. In URL provide your `YOUR_CLUSTER` and `YOUR_APP_KEY`.
3. After connecting, copy the "socket_id" and paste it into the "Broadcast auth" endpoint to receive an "auth" token.
4. Send a message to subscribe to a private task channel, for example:

```json
{
  "event": "pusher:subscribe",
  "data": {
    "auth": "TOKEN_FROM_BROADCAST_AUTH",
    "channel": "private-task.1" // where 1 is the task ID
  }
}
```
4. Change the status of the task with ID 1 via the API, and you will see the incoming message in Postman.

## ✅ Running Tests
To run the tests and verify the API's functionality, execute the command:

```bash
docker-compose exec php-cli php artisan test
```

Or

```bash
make cli
```

and after

```bash
php artisan test
```
