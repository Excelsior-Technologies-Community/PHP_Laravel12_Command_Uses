# PHP_Laravel12_Command_Uses

Laravel provides a powerful command-line interface called **Artisan** that helps developers perform common and advanced tasks efficiently. This guide covers the most essential Laravel Artisan commands, explained in a clean, structured, and beginner-friendly way.

---

## Installation & Setup Commands

### Create a New Laravel Project

```bash
# Using Composer
composer create-project laravel/laravel project-name

# Using Laravel Installer
laravel new project-name

# Specify Laravel version
composer create-project laravel/laravel project-name "12.*"
```

### Serve the Application

```bash
php artisan serve
php artisan serve --port=8080
php artisan serve --host=0.0.0.0 --port=8000
```

---

## Application Structure Commands

### Generate Controllers

```bash
php artisan make:controller UserController
php artisan make:controller UserController --resource
php artisan make:controller UserController --api
php artisan make:controller WelcomeController --invokable
php artisan make:controller UserController --model=User
```

### Generate Models

```bash
php artisan make:model User
php artisan make:model User -mfc
```

Options:

* `-m` Create migration
* `-c` Create controller
* `-r` Resource controller
* `-f` Factory
* `--seed` Seeder
* `-p` Pivot table

---
## add API functionality,
including the routes/api.php file and authentication scaffolding, run the following Artisan command in your Laravel 12 project's terminal: 
```bash
php artisan install:api
```
What This Command Does:-

Creates routes/api.php: In Laravel 11 and 12, the API routes file is no longer included by default to keep projects minimal. This command restores the file.

Installs Laravel Sanctum: It automatically installs the Laravel Sanctum package for token-based API authentication.

Registers Routes and Middleware: It adds the necessary configuration in bootstrap/app.php and applies the api middleware group (including rate limiting) to all routes defined in routes/api.php, and configures the HasApiTokens trait on your User model. 

## Database & Migration Commands

### Generate Migrations

```bash
php artisan make:migration create_users_table
php artisan make:migration add_status_to_users_table --table=users
php artisan make:migration remove_status_from_users_table --table=users
```

### Run Migrations

```bash
php artisan migrate
php artisan migrate --verbose
php artisan migrate:rollback
php artisan migrate:reset
php artisan migrate:refresh
php artisan migrate:refresh --seed
php artisan migrate:status
php artisan migrate:fresh
```

---

## Database & Seeding Commands

```bash
php artisan make:seeder UserSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed
php artisan make:factory UserFactory
php artisan db:seed --force
```

Additional Database Commands:

```bash
php artisan route:list
php artisan db:show
php artisan db:create database_name
```

---

## Routing & Middleware

```bash
php artisan make:middleware CheckAge
php artisan make:middleware AdminOnly --force
```

---

## Authentication Scaffolding

```bash
composer require laravel/breeze
php artisan breeze:install

composer require laravel/jetstream
php artisan jetstream:install livewire

composer require laravel/ui
php artisan ui bootstrap --auth
```

---

## Package Development

```bash
php artisan package:discover
php artisan package:discover --ansi
```

---

## Debugging & Testing

### Tinker

```bash
php artisan tinker
php artisan tinker --execute="echo 'Hello World';"
```

### Testing

```bash
php artisan test
php artisan test tests/Feature/UserTest.php
php artisan test --filter test_user_creation
php artisan test --coverage
php artisan test --parallel
```

### Debug Utilities

```bash
php artisan optimize:clear
php artisan env
php artisan list
php artisan help make:model
```

---

## Views & Blade Components

```bash
php artisan make:component Alert
php artisan make:component Alert --inline
php artisan make:component Alert --view
```

---

## Jobs & Queues

```bash
php artisan make:job SendWelcomeEmail
php artisan make:job ProcessPodcast --sync
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
php artisan queue:work
php artisan queue:work redis
php artisan queue:work --once
php artisan queue:table
```

---

## Events & Listeners

```bash
php artisan make:event OrderShipped
php artisan make:listener SendShipmentNotification
php artisan make:listener SendShipmentNotification --event=OrderShipped
php artisan event:generate
```

---

## Mail & Notifications

```bash
php artisan make:mail OrderShipped
php artisan make:notification InvoicePaid
php artisan make:notification InvoicePaid --markdown=mail.invoice.paid
```

---

## Cache & Optimization

```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan clear-compiled
php artisan optimize
php artisan route:cache
php artisan config:cache
php artisan event:cache
php artisan event:clear
```

---

## Security & Keys

```bash
php artisan key:generate
php artisan passport:install
php artisan passport:keys
```

---

## Storage & Maintenance

```bash
php artisan storage:link
php artisan down
php artisan up
php artisan down --secret="secret-key"
php artisan down --retry=60
```

---

## Scheduling

```bash
php artisan schedule:list
php artisan schedule:run
php artisan schedule:test
```

---

## Cleanup Commands

```bash
php artisan clear-compiled
php artisan optimize:clear
php artisan auth:clear-resets
```

---

## Stub Customization

```bash
php artisan stub:publish
```

---

## Custom Artisan Commands

```bash
php artisan make:command SendEmails
php artisan make:command SendEmails --command=emails:send
```

Example:

```php
protected $signature = 'emails:send {user} {--queue}';
protected $description = 'Send emails to users';

public function handle()
{
    $userId = $this->argument('user');
    $queue = $this->option('queue');

    $this->info('Emails sent successfully!');
}
```

---

## Daily Used Commands

```bash
php artisan serve
php artisan make:model Model -mfc
php artisan migrate
php artisan db:seed
php artisan tinker
php artisan test
```

---

## Production Deployment

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan migrate --force
php artisan storage:link
```

---

## Troubleshooting

```bash
composer dump-autoload
php artisan clear-compiled
php artisan cache:clear
```

---

## Quick Reference Cheatsheet

| Category | Command                            | Purpose                     |
| -------- | ---------------------------------- | --------------------------- |
| Server   | php artisan serve                  | Start development server    |
| Models   | php artisan make:model Model -mfc  | Create model with all files |
| Database | php artisan migrate                | Run migrations              |
| Database | php artisan migrate:refresh --seed | Refresh and seed            |
| Testing  | php artisan test                   | Run tests                   |
| Debug    | php artisan tinker                 | Interactive shell           |
| Cache    | php artisan cache:clear            | Clear cache                 |
| Auth     | composer require laravel/breeze    | Install auth                |

---

This guide covers most Laravel Artisan commands required for daily development and production use. Use `php artisan help <command>` for detailed command information.
