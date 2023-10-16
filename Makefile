
.PHONY: pages serve

# Inputs
PAGES  := index.php
JS := $(shell find scripts/reload.js)

# Outputs
HTML_FILES := $(PAGES:%.php=site/%.html)
JS_FILES := $(JS:%.js=site/%.js)

site/%.html: %.php
	@mkdir -p site/
	php $^ > $@

pages: $(HTML_FILES)

build: pages $(JS_FILES)
	cp -r scripts site/

site/%.js: %.js
	@# Currently playing it safe by doing each file individually
	@# Might see if doing all at once is worthwhile once I have
	@# a few files
	closure-compiler -O ADVANCED $^ --js_output_file $@

clean:
	rm -rf build
	rm -rf site

serve: ## Development server
	php -S 127.0.0.1:8080
