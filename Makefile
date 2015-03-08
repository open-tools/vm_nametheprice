BASE=nametheprice
PLUGINTYPE=vmcustom
VERSION=1.3
FILEBASE=opentools_vm2

PLUGINFILES=$(BASE).php $(BASE).script.php $(BASE).xml index.html

TRANSLATIONS=$(call wildcard,language/*/*.plg_$(PLUGINTYPE)_$(BASE).*ini) 
INDEXFILES=language/index.html $(call wildcard,language/*/index.html)
TMPLFILES=$(call wildcard,$(BASE)/tmpl/*.php) $(call wildcard,$(BASE)/tmpl/index.html) $(call wildcard,$(BASE)/index.html)  
ASSETS=
ZIPFILE=plg_$(FILEBASE)_$(BASE)_v$(VERSION).zip


zip: $(PLUGINFILES) $(TRANSLATIONS) $(ELEMENTS) $(INDEXFILES) $(TMPLFILES) $(ASSETS)
	@echo "Packing all files into distribution file $(ZIPFILE):"
	@zip -r $(ZIPFILE) $(PLUGINFILES) $(TRANSLATIONS) $(ELEMENTS) $(INDEXFILES) $(TMPLFILES) $(ASSETS)

clean:
	rm -f $(ZIPFILE)
