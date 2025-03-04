# JunkHop Development
**JunkHop: A Digital Solution for Sustainable Waste Management and Community Recycling**

<!-- TOC -->
### Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Development](#development)
- [Contributing](#contributing)
- [License](#license)

<!-- /TOC -->

## Overview

JunkHop is a web application developed using Laravel and Nuxt.js. The project aims to provide a seamless experience for managing user roles and permissions, with a focus on integrating Laravel Sanctum for authentication.

## Features

- User management with role-based access control
- Integration with Laravel Sanctum for authentication
- API endpoints for various functionalities
- Docker support for development and deployment
- Automated database migrations and seeding

## Requirements

- PHP ^8.2
- Node.js
- Docker

## Installation

1. Clone the repository:
    ```shell
    git clone https://github.com/your-repo/cleansnap.git
    cd cleansnap
    ```

2. Install PHP dependencies:
    ```shell
    composer install
    ```

3. Install Node.js dependencies:
    ```shell
    npm install
    ```

4. Set up environment variables:
    ```shell
    cp .env.example .env
    ```

5. Generate application key:
    ```shell
    php artisan key:generate
    ```

6. Build Docker Containers:
    ```shell
    docker-compose build
    ```

## Usage

1. Start the Docker containers:
    ```shell
    docker-compose up
    ```

2. Run database migrations and seeders::
    ```shell
    docker exec -it cleansnap-development-laravelnuxt.api-1 php artisan migrate --seed
    ```

3. Access the application
  * Frontend: http://localhost:3000
  * Backend: http://localhost:8000

## Development

* To start the development server for Nuxt.js:
    ```shell
    npm run dev
    ```

* To run Laravel Octane for the backend
    ```shell
    npm run api
    ```

### Contributing
Please read [CONTRIBUTING.md](/) for details on our code of conduct and the process or submitting pull requests.

### License
The project is licensed under the MIT License - see the [LICENSE](/LICENSE) file for details.
