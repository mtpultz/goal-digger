# Goal Digger

A goal tracker for those chasing goals like they’re rich, shiny, and mildly afraid of commitment.

## Setup

### Install VSCode

- Download and install [VSCode](https://code.visualstudio.com)
- Install the recommended extensions for the repository

### Install Herd

- Download and install [Laravel Herd](https://herd.laravel.com), which provides a development environment with Laravel, PHP, and a Nginx server that runs in the background

### Install Postgres

- Run `brew install postgresql@17` to install the latest version of **Postgres**
- Run `brew services start postgresql@17` to automatically launch **Postgres** at login
- Optionally, if access to `psql` is needed add it to your `PATH` in `.zshrc` using:

    ```bash
    export PATH="/opt/homebrew/opt/postgresql@17/bin:$PATH"
    ```

### Install DBeaver

- Download [DBeaver](https://dbeaver.io/download)
- Connect to **Postgres**, which will either use the default username `postgres`, or the your `macOS` username
- Create a new database named `goal_digger`

### Clone and Setup Application

- Clone the [repository](https://github.com/mtpultz/goal-digger) into the `~/Herd` that was created during the installation of **Laravel Herd**
- Run `composer install`
- Run `npm install`
- Copy `.env.example` to `.env` and update the `DB_PASSWORD` with your root password
- Run `php artisan migrate:fresh` to run all the migrations
- Run `php artisan db:seed` to add seed data to the database
- Run `composer run dev` to start the application, and
- Visit `http://goal-digger.test`
