all: docs vendor/bootstrap/bootstrap

docs:
	@echo Fetching/Updating $@.
	@git submodule update --init $@

vendor/bootstrap/bootstrap:
	@echo Fetching/Updating $@.
	@git submodule update --init $@
	@cd $(BOOTSTRAPDIR); $(MAKE) $(MAKEFLAGS) bootstrap
	@ln -sf ../../$@ static/themes/bootstrap

.PHONY: all
