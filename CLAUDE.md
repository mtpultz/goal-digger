# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Frontend (Node.js/React)
- `npm run dev` - Start Vite development server
- `npm run build` - Build for production
- `npm run lint:check` - Check ESLint issues
- `npm run lint:fix` - Fix ESLint issues automatically
- `npm run format:check` - Check Prettier formatting
- `npm run format:write` - Format code with Prettier
- `npm run spell:check` - Check spelling with cspell

### Backend (Laravel/PHP)
- `composer run dev` - Start full development environment (Laravel server, queue worker, logs, and Vite)
- `composer run test` - Run PHP tests using Pest
- `composer run lint:check` - Check PHP code style with Duster
- `composer run lint:fix` - Fix PHP code style issues with Duster
- `php artisan migrate:fresh` - Reset database and run all migrations
- `php artisan db:seed` - Seed the database with test data
- `php artisan passport:client --client` - Create OAuth client for API access

### Testing
- Backend tests use Pest framework and are located in `tests/` directory
- Run specific test: `php artisan test tests/Feature/Goals/APIGoalGetActiveTest.php`
- Tests use SQLite in-memory database for isolation

## Architecture Overview

### Backend (Laravel 12)
- **Framework**: Laravel 12 with PHP 8.2+
- **Authentication**: Laravel Passport (OAuth2) for API authentication
- **Database**: PostgreSQL (development), SQLite (testing)
- **Key Models**: User, Goal, Comment, Buddy, BuddyGoal
- **API Routes**: Defined in `routes/api.php` with `auth:api` middleware for protected endpoints

### Frontend (React 19 + TypeScript)
- **Framework**: React 19 with TypeScript and Vite
- **Routing**: React Router DOM v7
- **Styling**: Tailwind CSS v4 with shadcn/ui components
- **Forms**: React Hook Form with Zod validation
- **State Management**: React Context (AuthContext) for authentication
- **Build Tool**: Vite with Laravel integration

### Database Schema
- **Goals**: Hierarchical structure with parent-child relationships, categories, completion tracking
- **Comments**: Associated with goals, support for goal progress updates
- **Buddies**: User relationships for goal sharing and accountability
- **Users**: Standard Laravel authentication with Passport tokens

### Key Features
- **Hierarchical Goals**: Goals can have parent-child relationships for sub-goals
- **Goal Views**: Multiple views including tree view, streamlined view, and detailed views
- **Authentication Flow**: Registration, login, email verification, password reset
- **API-First**: React frontend communicates with Laravel backend via REST API
- **Real-time Updates**: Goal progress tracking and commenting system

### File Structure Notes
- Frontend components in `resources/js/components/`
- API controllers in `app/Http/Controllers/`
- Database migrations in `database/migrations/`
- Tests organized by feature in `tests/Feature/`
- Laravel models follow standard naming conventions in `app/Models/`

### OAuth Setup
The application uses Laravel Passport for API authentication. After setting up a client with `php artisan passport:client --client`, use the CLIENT_ID and CLIENT_SECRET for API requests. See README.md for detailed Postman setup instructions.