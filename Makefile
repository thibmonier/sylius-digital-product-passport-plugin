.DEFAULT_GOAL := help

help: ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	composer install --no-scripts

phpstan: ## Run PHPStan
	vendor/bin/phpstan analyse

ecs: ## Run ECS
	vendor/bin/ecs check

ecs-fix: ## Fix coding standards
	vendor/bin/ecs check --fix

rector: ## Run Rector (dry-run)
	vendor/bin/rector process --dry-run

rector-fix: ## Apply Rector fixes
	vendor/bin/rector process

test: ## Run tests
	vendor/bin/phpunit

quality: phpstan ecs rector ## Run all quality checks

.PHONY: help install phpstan ecs ecs-fix rector rector-fix test quality
