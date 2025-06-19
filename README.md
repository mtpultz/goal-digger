# Goal Digger

A goal tracker for those chasing goals like they’re rich, shiny, and mildly afraid of commitment.

## Table of Contents

1. [Install Dependencies](#install-dependencies)
1. [Setup Application](#setup-application)
1. [Using Access Tokens](#using-access-tokens)

## Install Dependencies

### Install VSCode

-   Download and install [VSCode](https://code.visualstudio.com)
-   Install the recommended extensions for the repository

### Install Herd

-   Download and install [Laravel Herd](https://herd.laravel.com), which provides a development environment with Laravel, PHP, and a Nginx server that runs in the background

### Install Postgres

-   Run `brew install postgresql@17` to install the latest version of **Postgres**
-   Run `brew services start postgresql@17` to automatically launch **Postgres** at login
-   Optionally, if access to `psql` is needed add it to your `PATH` in `.zshrc` using:

    ```bash
    export PATH="/opt/homebrew/opt/postgresql@17/bin:$PATH"
    ```

### Install DBeaver

-   Download [DBeaver](https://dbeaver.io/download)
-   Connect to **Postgres**, which will either use the default username `postgres`, or the your `macOS` username
-   Create a new database named `goal_digger`

## Setup Application

-   Clone the [repository](https://github.com/mtpultz/goal-digger) into the `~/Herd` that was created during the installation of **Laravel Herd**
-   Run `composer install`
-   Run `npm install`
-   Copy `.env.example` to `.env` and update the `DB_PASSWORD` with your root password
-   Run `php artisan migrate:fresh` to run all the migrations
-   Run `php artisan db:seed` to add seed data to the database
-   Run `composer run dev` to start the application, and
-   Visit `http://goal-digger.test`

## Using Access Tokens

-   Install HTTP client of choice
    -   [Postman](https://www.postman.com/downloads)
    -   [Insomnia](https://insomnia.rest/download)
-   Run `php artisan passport:client --client`
-   Set the default client name of **Goal Digger**
-   Copy the `CLIENT_ID` and `CLIENT_SECRET` into the HTTP client local environment variables

### Postman Setup

-   Create a local environment with:

    ```
    BASE_URL: http://goal-digger.test
    CLIENT_ID: YOUR_CLIENT_ID
    CLIENT_SECRET: YOUR_CLIENT_SECRET
    CLIENT_USERNAME: YOUR_USERNAME
    CLIENT_PASSWORD: YOUR_PASSWORD
    ACCESS_TOKEN: SET_DYNAMICALLY_LEAVE_EMPTY
    ```

-   Create `JSON` headers preset with:

    ```bash
    Accept:application/json
    Content-Type:application/json
    ```

-   Create a separate `Auth` preset header with:

    ```
    Client-Id:{{CLIENT_ID}}
    Client-Secret:{{CLIENT_SECRET}}
    ```

-   Create a `GET {{BASE_URL}}/oauth/token` endpoint to dynamically set the `ACCESS_TOKEN` environment variable on a successful response, add the `JSON` headers preset, and add the following test script:

    ```js
    pm.test("response is ok", () => {
        pm.response.to.have.status(200);
    });

    var jsonData = JSON.parse(responseBody);

    pm.environment.set("ACCESS_TOKEN", jsonData.access_token);
    ```

-   Store the endpoint into a collection named `OAuth2`
-   Make a request to `GET {{BASE_URL}}/oauth/token`
-   Verify that `ACCESS_TOKEN` was set in the local environment
-   Create a `POST {{BASE_URL}}/api/register` endpoint
-   Store the endpoint into a collection named `Users`
-   On the collection set the `Authorization` to `Auth Type` of `Bearer Token`, and set the `Token` to `{{ACCESS_TOKEN}}`
-   On the `POST {{BASE_URL}}/api/register` endpoint set `Authorization` to `Inherit auth from parent`
-   Add the `JSON` and `Auth` preset headers
-   Add a registration request payload of:

    ```json
    {
        "name": "Registration Example",
        "email": "registration@example.com",
        "password": "password",
        "password_confirmation": "password"
    }
    ```

-   Make a request to `POST {{BASE_URL}}/api/register`, which should insert a new user into the `Users` table
