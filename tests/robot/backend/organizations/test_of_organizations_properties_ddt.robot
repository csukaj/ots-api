*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Organization
#Test Template			Properties Section Check All Template
#Test Template			Properties Section Main Template
#Test Template			Properties Section Child Class Template
Test Template			Properties templete
Default Tags			test_develop
#Test Teardown			Close Browser

*** Test Cases ***
	#						Hotel		Create/Edit/Delete		Tab		Meta/Class	Name;;;		Searchable	Value;;;	Desc;;; 	Value;;;	Desc;;; 	Charge		Highlighted		Listable	Prio
#Test Temp					Hotel A		Create		Facilities	Child classifications	Pool and wellness	Some Value	Some Desc	Some Value	Some Desc	Charge	Hl	Listable	Prio
Create New Child Class		Hotel H		New		Facilities	Child classification	Lighting	true	null	Value2	account		Mom, there's something wrong with nana and papa.;Mom, es ist etwas falsch mit nana und Papa.;Anyu, valami gond van a nagyival és a papával.;Мама, есть что-то не так с Нана и папой.	free	oil		false			true		19
Create New Child Class2		Hotel H		New		Facilities	Child classification	Lighting	false	null	Value2	adjust		Mom, there's something wrong with nana and papa.;Mom, es ist etwas falsch mit nana und Papa.;Anyu, valami gond van a nagyival és a papával.;Мама, есть что-то не так с Нана и папой.	free	ninja	false			true		19
Edit Created Child Class	Hotel H		Edit		Facilities	Child classification	Lighting	true	null	Value3	adjust	Mom, there's something wrong with nana and papa.;Mom, es ist etwas falsch mit nana und Papa.;Anyu, valami gond van a nagyival és a papával.;Мама, есть что-то не так с Нана и папой.	free	note		false			true		21
Delete Created Child Class	Hotel H		Delete		Facilities	Child classification	Lighting
Create New Child Meta		Hotel H		New		Facilities	Child meta	Number of rooms	nfc	108		If you must blink, do it now.;;Ha pislognod kell, most pislogj;		true		19
Edit Created Child Meta		Hotel H		Edit		Facilities	Child meta	Number of rooms	npm	108		If you must blink, do it now.;;Ha pislognod kell, most pislogj;		false		21
Delete Created Child Meta	Hotel H		Delete		Facilities	Child meta	Number of rooms
#Main New Template			Hotel C		New		Lighting;Beleuchtung;;освещение		free;frei;;бесплатно		false			true		19
#Main New Template			Hotel C		New		Lighting		free		false			true		19
#Main New Template			Hotel C		New		Lighting		almost free;beinahe frei;Majdnem Ingyen;Почти бесплатно		false			true		19
#Main New Template			Hotel C		New		Lighting;Beleuchtung;;освещение		almost free		false			true		19
#Main New Template			Hotel C		New		Lighting;Beleuchtung;Világítás;освещение		almost free		false			true		19
#Main New Template			Hotel C		New		Lighting		almost free		false			true		19
#Main Edit Template			Hotel B		Edit	Equipment	surcharged			true			false		4
#Main Edit Template2			Hotel B		Edit	Equipment	free			false			true		3
#Name						Hotel Name	Type	Row			Name	Value					Description			Charge	Hl		Listable	Priority
#Child Class New Template	Hotel B		New		Stuff	WiFee	${EMPTY}	${EMPTY}		${EMPTY}	true	true		${EMPTY}
#	[Template]	Properties Section Child Class Template
#Child Class Edit Template	Hotel B		Edit	Stuff	WiFee	Unlimited Data Usage	Unlimited Usage EDIT		free	true	false		4
#	[Template]	Properties Section Child Class Template
#Child Class Edit Template	Hotel A		Edit	Facilities	Wireless Internet;Drahtloser Internet;;Беспроводное в Интернет	Unlimited;Unbegrenzt;Korlátlan;неограниченный	Unlimited Usage DELETE SOON		free	true	false		5
#Child Class Edit Template	Hotel A		Edit	Facilities	Wireless Internet	Unlimited;Unbegrenzt;Korlátlan;неограниченный	Unlimited Usage DELETE SOON		free	true	false		5
#Child Class Edit Template	Hotel A		Edit	Facilities	Wireless Internet	Unlimited	Description of a funny story;Beschreibung einer lustigen Geschichte;Egy vicces történet leírása;Описание анекдота	free	true	false		5
#Child Class Edit Template	Hotel A		Edit	Facilities	Wireless Internet	Unlimited	Description of a funny story	free;frei;Ingyenes;бесплатно	true	false		2
##Name						Hotel Name	Type	Row			Name	Val (NUM)	Description			Listable	Priority
#Child Meta New Template		Hotel B		New		Stuff	Dishes	108			Fresh fried fish	true		1
#	[Template]	Properties Section Child Metas Template
#Child Meta Edit Template	Hotel B		Edit	Stuff	Dishes	109			Fresh EDITED fish	false		2
#	[Template]	Properties Section Child Metas Template
#Child Meta Edit Template2	Hotel A		Edit	General		Number of rooms		117			Mom, there's something wrong with nana and papa.;Mom, es ist etwas falsch mit nana und Papa.;Anyu, valami gond van a nagyival és a papával.;Мама, есть что-то не так с Нана и папой.	true		1
##Child Meta Delete Template	Hotel A		Delete	Facilities	Dishes	107			Fresh EDITED pea	true		1
#Child Class Delete Template	Hotel B		Delete	Facilities	WiFee	Unlimited Data Usage	Unlimited Usage EDIT		free	true	false		4
#	[Template]	Properties Section Child Class Template
#Child Meta Delete Template	Hotel B		Delete	Stuff	Dishes	109			Fresh EDITED fish	false		2
#	[Template]	Properties Section Child Metas Template
##Main Delete Template		Hotel A		Delete	Facilities	free			false			true		1
#Check All Impossible Row	Hotel A
#	[Template]	Properties Section Check All Template