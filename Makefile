BOOTSTRAPDIR = vendor/bootstrap
DOCSDIR = docs

all: docs twitter/bootstrap

docs:
	@echo Fetching/Updating $@.
	@git submodule update --init $(DOCSDIR)

twitter/bootstrap:
	@echo Fetching/Updating $@.
	@git submodule update --init $(BOOTSTRAPDIR)
	@cd $(BOOTSTRAPDIR); $(MAKE) $(MAKEFLAGS) bootstrap
	@ln -sf ../../$(BOOTSTRAPDIR)/bootstrap static/themes/bootstrap

.PHONY: all docs twitter/bootstrap
