# "c:\Program Files (x86)\GnuWin32"\bin\make -f LRM.mk
TOOLS = c:/tools
SEVENZ = "c:\Program Files\7-Zip\7z"

# see http://www.l3s.de/~minack/rdf2rdf/
CONV = $(TOOLS)/rdf2rdf-1.0.1-2.3.1.jar 
OTHER = other-schemas

# JAVA = java -version:1.7 
JAVA = java 
PYTHON = "c:\Python278\python"

PELLET = $(JAVA) -jar $(TOOLS)/pellet-2.3.1/lib/pellet-cli.jar 
VERSION = v2.2.1

%.rdf : %.ttl
	@$(JAVA) -jar $(CONV) $< $@

%.rdfs : %.ttl
	@$(JAVA) -jar $(CONV) $< $@

%.nt : %.ttl
	@$(JAVA) -jar $(CONV) $< $@
	
%.owl : %.ttl
	@$(JAVA) -jar $(CONV) $< $@
	
# %.ttl : %.rdf
	# $(JAVA) -jar $(CONV) $< $@

time.ttl : $(OTHER)/time.owl
	$(JAVA) -jar $(CONV) $< $@
	
all:  LRM LRM.zip
	
clean:
	del *.rdf
	del *.nt
	del LRM.zip

ttl-ontologies : $(OTHER)/bibo.ttl $(OTHER)/UDFR-onto.ttl $(OTHER)/timezone-world.ttl $(OTHER)/cidoc-v5.1.ttl $(OTHER)/opmo-20101012.ttl $(OTHER)/bom.ttl

%.classification.txt : %.ttl
	$(PELLET)  classify $<
	
	
LRM.zip: LRM LRM-package-list.txt
	$(SEVENZ) a LRM-$(VERSION).zip @LRM-package-list.txt
 
# LRM:  LRMstatic LRMdynamic LRMversion LRMschema LRMreal LRMreflexive LRMexamples
LRM:  LRMstatic LRMdynamic LRMversion LRMschema LRMreal LRMreflexive
	
LRMschema:  LRM-schema.rdf LRM-schema.nt
	@echo done

LRMstatic:  LRM-static-schema.rdf LRM-static-schema.nt
	@echo done
	
LRMdynamic: TIMEschema LRM-dynamic-schema.rdf LRM-dynamic-schema.nt
	@echo done

LRMversion: LRMstatic LRMdynamic TIMEschema LRM-semantic-versioning-schema.rdf LRM-semantic-versioning-schema.nt
	@echo done

LRMreal: LRM-ReAL-schema.rdf LRM-ReAL-schema.nt
	@echo done
    
LRMreflexive: LRM-reflexive.rdf LRM-reflexive.nt
	@echo done
    
TIMEschema: time-schema.nt time-schema.rdf timezone-world.nt time.ttl
	@echo done
    
LRMexamples: BillViola LRM-change-document

BillViola: LRMversion LRM-viola-example.nt 
	@$(PELLET) consistency -v --input-format Turtle LRM-viola-example.ttl
	@echo done

LRM-change-document: LRMstatic LRMdynamic TIMEschema
	@$(PELLET) consistency -v --input-format Turtle LRM-change-document.ttl
	@echo done


LRM.nt: LRM-static-schema.nt LRM-dynamic-schema.nt LRM-semantic-versioning-schema.nt time-schema.nt LRM-schema.nt
	copy /Y LRM-static-schema.nt+LRM-dynamic-schema.nt+LRM-semantic-versioning-schema.nt+time-schema.nt+LRM-schema.nt $@
	#@$(PYTHON) ntcat.py $< $@
	@echo done

AllInOne: LRMclean LRM.nt
	@echo done

LRMclean: LRM.nt
	del LRM.nt

LRM.ttl: 
	@$(JAVA) -jar $(CONV) LRM.nt LRM.ttl
	@echo done

LRMcheck:   LRM
	@$(PELLET) consistency -v --input-format Turtle LRM-schema.ttl
	$(PELLET) consistency -v --input-format Turtle LRM-change-document.ttl
	@echo done

Render: RenderFile_SW.rdf
	$(PELLET) consistency RenderFile_SW.ttl
   
