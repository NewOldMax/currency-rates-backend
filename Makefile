project_name = "currency_rates"
current_dir = $(shell pwd)

jwt_keys_dir = $(current_dir)/src/Resources/jwt

.PHONY: build install gen-jwt-keys

install:
	@echo Install dependencies
	docker run --rm -v $(current_dir):/app composer/composer install --ignore-platform-reqs

update:
	@echo Update dependencies
	docker run --rm -v $(current_dir):/app composer/composer update --ignore-platform-reqs

apidoc:
	@echo Generate Api Documentation
	docker run --rm -ti -v $(current_dir):/docs  humangeo/aglio --theme-template triple -i src/Resources/docs/api_documentation.md -o devops/dockerfiles/nginx/web/api_documentation.html

gen-jwt-keys:
	sh ./devops/scripts/generate_jwt_keys

fill-fake-data:
	@echo Fill Fake data for develop purposes
	sh ./devops/scripts/populate_data.sh


build: install apidoc gen-jwt-keys
