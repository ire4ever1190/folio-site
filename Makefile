
.PHONY: pages serve

PAGES  := index.php
HTML_FILES := $(PAGES:%.php=%.html)

%.html: %.php
	php $^ > $@

pages: $(HTML_FILES)

clean:
	rm -f $(HTML_FILES)

serve: ## Development server
	php -S 127.0.0.1:8080
