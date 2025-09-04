OS := $(shell uname -s)

.DEFAULT_GOAL := help

ifeq ($(OS),Linux)
    DOCKER_COMPOSE = docker compose
else ifeq ($(OS),Darwin)
    DOCKER_COMPOSE = docker-compose
else
    DOCKER_COMPOSE = docker-compose
endif

install: ## Install project
	$(DOCKER_COMPOSE) build
	$(MAKE) start
	$(DOCKER_COMPOSE) run app composer install

start: ## Run project
	$(DOCKER_COMPOSE) up -d

stop: ## Stop project running
	$(DOCKER_COMPOSE) down --remove-orphans

bash: ## Enter to bash a container php
	$(DOCKER_COMPOSE) exec app bash

kill-all: ## Kill all running containers
	docker container kill $$(docker container ls -q)

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
