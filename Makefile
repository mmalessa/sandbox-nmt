.DEFAULT_GOAL = help
APP_USER_ID     ?= $(shell id -u)
DC = docker compose
#-----------------------------------------------------------------------------------------------------------------------

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

.PHONY: build
build: ## build image
	@$(DC) build

.PHONY: init
init: up
	@$(DC) exec php sh -c "composer config platform.php 8.4; composer install"

.PHONY: up
up: ## Up all
	@$(DC) up -d

.PHONY: down
down: ## Down all
	@$(DC) down

.PHONY: sh
sh: ## Enter application shell
	@$(DC) exec -it php bash

# https://ollama.com/library
.PHONY: get-models ## Download Ollama models
get-models:
	@#$(DC) exec -it ollama ollama pull qwen2.5:3b
	@$(DC) exec -it ollama ollama pull qwen3:1.7b
	@#$(DC) exec -it ollama ollama pull mistral:7b-instruct
	@#$(DC) exec -it ollama ollama pull mistral:7b
#	@$(DC) exec -it ollama ollama pull nomic-embed-text:latest


.PHONY: list-models ## Download Ollama models
list-models:
	@$(DC) exec -it ollama ollama list
