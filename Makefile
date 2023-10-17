
.PHONY: pages serve

# Inputs
PAGES  := index.php
JS := $(shell find scripts/ -not -name reload.js)
CSS := $(shell find css/)

# Outputs
HTML_FILES := $(PAGES:%.php=site/%.html)
JS_FILES := $(JS:%.js=site/%.js)
CSS_FILES := $(CSS:%.css=site/%.css)


site/%.html: %.php
	@mkdir -p site/css
	php $< > $@

site/%.css: %.css site/
	@# TODO: Optimise the CSS
	@# Maybe also combine all the CSS into one file?
	@mkdir -p site/css
	cp $< $@


pages: $(HTML_FILES)

build: pages $(JS_FILES) $(CSS_FILES)
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
	php -S 127.0.0.1:8080
