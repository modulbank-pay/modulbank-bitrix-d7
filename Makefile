package: clean
	mkdir .last_version
	find install -type d | xargs -n1 -I@ mkdir -p .last_version/@
	find install -type f -name \*.php | xargs -n1 -I@ sh -c 'cat @ | iconv -t cp1251 > .last_version/@'
	find install -type f -not -name \*.php | xargs -n1 -I@ sh -c 'cat @ > .last_version/@'
	#find install -type f | xargs -n1 -I@ sh -c 'cat @ > .last_version/@'
	cp include.php .last_version/
	tar czf .last_version.tgz .last_version
	@echo "Done. Check out .last_version.tgz"

clean:
	rm -rf .last_version .last_version.tgz
