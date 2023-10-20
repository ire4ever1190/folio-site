
.PHONY: pages serve clean build

# Inputs
PAGES  := index.php
JS := $(shell find scripts/ -not -name reload.js)
CSS := $(shell find css/ -name "*.css")

# Outputs
HTML_FILES := $(PAGES:%.php=site/%.html)
JS_FILES := $(JS:%.js=site/%.js)

site/:
	mkdir -p site/

site/%.html: %.php site/
	php $< > $@

site/css/site.css: $(CSS)
	@# TODO: Optimise the CSS
	@mkdir -p site/css
	cat $^ > $@

site/favicon.svg: favicon.php site/
	php $< > $@

pages: $(HTML_FILES)

build: site/ pages $(JS_FILES) $(CSS_FILES) site/favicon.svg site/css/site.css
	cp -r scripts site/
	cp -r css site/
	cp -r imgs site/

site/%.js: %.js
	@# Currently playing it safe by doing each file individually
	@# Might see if doing all at once is worthwhile once I have
	@# a few files
	closure-compiler -O ADVANCED $^ --js_output_file $@


clean:
	rm -rf build
	rm -rf site

serve: ## Development server
	PHP_CLI_SERVER_WORKERS=4 php -S 127.0.0.1:8080
