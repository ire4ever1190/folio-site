
.PHONY: pages serve clean build

PHP_FLAGS ?= \
	-dopcache.enable=1 \
	-dopcache.enable_cli=1 \
	-dopcache.jit_buffer_size=256M \
	-dopcache.jit=tracing
PHP_CMD = php $(PHP_FLAGS)

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
	$(PHP_CMD) $< > $@

site/sitemap.xml: sitemap.php CNAME $(HTML_FILES)
	$(PHP_CMD) $< $(HTML_FILES) > $@

site/css/site.css: $(CSS)
	@# TODO: Optimise the CSS
	@mkdir -p site/css
	cat $^ > $@

site/favicon.svg: favicon.php site/
	$(PHP_CMD) $< > $@

site/imgs/banner.gif: tools/gif/gifs/infinity.php tools/gif/Frame.php tools/gif/GIFBuilder.php
	mkdir -p site/imgs/
	$(PHP_CMD) $< > $@

pages: $(HTML_FILES)

build: site/ pages $(JS_FILES) $(CSS_FILES) site/favicon.svg site/css/site.css site/sitemap.xml site/imgs/banner.gif
	cp -r css site/
	cp -r imgs site/


site/%.js: %.js closure-compiler.jar
	@# Currently playing it safe by doing each file individually
	@# Might see if doing all at once is worthwhile once I have
	@# a few files.
	mkdir -p $(@D)
	java -jar closure-compiler.jar -O ADVANCED $< --js_output_file $@


clean:
	rm -rf build
	rm -rf site
	rm -rf closure-compiler.jar

serve: ## Development server
	PHP_CLI_SERVER_WORKERS=4 php $(PHP_FLAGS) -S 127.0.0.1:8080

#
# Dependencies:
#

CLOSC_VER ?= v20230802

closure-compiler.jar:
	curl https://repo1.maven.org/maven2/com/google/javascript/closure-compiler/$(CLOSC_VER)/closure-compiler-$(CLOSC_VER).jar -o $@
