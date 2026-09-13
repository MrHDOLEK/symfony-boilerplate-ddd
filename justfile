set shell := ["bash", "-uc"]

dc := env_var_or_default("DOCKER_COMPOSE", if os() == "linux" { "docker compose" } else { "docker-compose" })

_default:
    @just --list --list-heading $'Available commands:\n'

# Init project
[group('setup')]
install:
    cp -n .env.dist .env
    {{ dc }} build
    {{ dc }} run app composer install
    {{ dc }} run app composer tools:install

# Install isolated dev tools (tools/*: phpstan, cs-fixer, deptrac)
[group('setup')]
tools-install:
    {{ dc }} run app composer tools:install

# Update isolated dev tools to their latest allowed versions (bumps each tools/*/composer.lock)
[group('setup')]
tools-update:
    {{ dc }} run app composer tools:update

# Run docker for a project
[group('docker')]
start:
    {{ dc }} up -d

# Stop all containers for a project
[group('docker')]
stop:
    {{ dc }} down --remove-orphans

# Exec bash for app container
[group('docker')]
bash:
    {{ dc }} exec app bash

# Kill all running containers
[group('docker')]
kill-all:
    docker container kill $(docker container ls -q)

# Change permission for volume in the app container
[group('docker')]
fix-permissions:
    {{ dc }} exec app usermod -u 1000 www-data

# Static analysis via isolated tools/phpstan (src + config + tests)
[group('quality')]
phpstan:
    {{ dc }} exec app composer phpstan

# Run tests for a app container
[group('quality')]
phpunit:
    {{ dc }} exec app composer test

# Run the Behat feature suite
[group('quality')]
behat:
    {{ dc }} exec app composer behat

# Check code style via isolated tools/cs-fixer
[group('quality')]
cs-check:
    {{ dc }} exec app composer cs:check

# Fix code style via isolated tools/cs-fixer
[group('quality')]
cs-fix:
    {{ dc }} exec app composer cs:fix

# Architecture guard via isolated tools/deptrac
[group('quality')]
deptrac:
    {{ dc }} exec app composer test:architecture

# Run stage for test
[group('quality')]
run-tests: cs-check phpstan deptrac phpunit behat

# Run composer update for app container
[group('app')]
composer-update:
    {{ dc }} exec app composer update

# Run migrations
[group('app')]
migrate:
    {{ dc }} exec app composer migrate

# Clear the Symfony cache
[group('app')]
cache-clear:
    {{ dc }} exec app php bin/console cache:clear

# Lint the Helm chart in .k8s
[group('k8s')]
helm-lint:
    helm lint .k8s

# Render the Helm chart in .k8s with the default values
[group('k8s')]
helm-template:
    helm template symfony-app .k8s
