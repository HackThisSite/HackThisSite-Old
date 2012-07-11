.PHONY: all clean

all: twitter/bootstrap

twitter/bootstrap:
	@echo 'Building twitter-bootstrap.'
	cd vendor/bootstrap; $(MAKE) $(MAKEFLAGS) bootstrap

clean:
