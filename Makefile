BOOTSTRAPDIR = vendor/bootstrap
DOCSDIR = docs

all: docs twitter/bootstrap

docs:
	git submodule update --init $(DOCSDIR)

twitter/bootstrap:
	git submodule update --init $(BOOTSTRAPDIR)
	cd vendor/bootstrap; $(MAKE) $(MAKEFLAGS) bootstrap
	ln -sf ../../vendor/bootstrap/bootstrap static/themes/bootstrap

.PHONY: all
