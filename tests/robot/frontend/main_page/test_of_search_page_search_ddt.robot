*** Settings ***
Documentation			Frontend Test for OTS Main Page

Resource				../../resource/resource.robot
Resource				../resource/resource_variables_languages.robot
Resource				../resource/resource_variables.robot
Resource				../resource/resource_basic_functions.robot
Resource				../../backend/resource/resource_helpers.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
#Resource				../resource/resource_variables.robot
#Suite Setup				Given User Visit The OTS Search Site with "${BROWSER_NAME}" browser
#Test Setup				Conditional Navigation From JSON	${OUTPUT_DIR}/test_jsons/search_page_2026.json
Test Template			Search Page Search Template

*** Variables ***
@{CUSTOMER_1}			Wenona		Townsend	United States	wenonats@test.com	06505047878
@{DEFAULT_GUEST1}		Kerrie		Jardine
@{DEFAULT_GUEST2}		Alfreda		Pál
@{DEFAULT_GUEST3}		Rena		Sheppard
@{DEFAULT_GUEST4}		Noelle		Short
@{DEFAULT_GUEST5}		Marjory		Beck
@{DEFAULT_GUEST6}		Davie		Spears
@{DEFAULT_GUEST7}		Isebella	Langer
@{DEFAULT_GUEST8}		Gerhardt	Rupertson
@{DEFAULT_GUEST9}		Kunibert	Merchant
${SPECIAL_REMARKS}		In the aftermath of WWI, a young German who grieves the death of her fiancé in France meets a mysterious Frenchman who visits the fiancé's grave to lay flowers.

@{ALL_GUESTS}			@{DEFAULT_GUEST1}	@{DEFAULT_GUEST2}	@{DEFAULT_GUEST3}	@{DEFAULT_GUEST4}	@{DEFAULT_GUEST5}	@{DEFAULT_GUEST6}	@{DEFAULT_GUEST7}	@{DEFAULT_GUEST8}	@{DEFAULT_GUEST9}

*** Test Cases ***
#						From(MM/DD/YYYY or unknown)
#						|				To(MM/DD/YYYY or unknown)
#						|				|				Guests(a;c:x,y,z/a;c:x,y,z)
#						|				|				|						Type of(H/H/A)
#Sample Search Case		01/20/2017		01/21/2017		1;2:1,2/3;4:4,3,2,1		Holiday					Name		Island Name (or EMPTY)	MP HC
Sample Search Case A		06/11/2026		06/18/2026		2;0					Holiday		${EMPTY}		${EMPTY}	${EMPTY}	${EMPTY}	true
	[Setup]			Conditional Navigation From JSON	${OUTPUT_DIR}/test_jsons/search_page_2026.json
#Sample Search Case B		06/11/2026		06/18/2026		20;0					Holiday		${EMPTY}		${EMPTY}	${EMPTY}	${EMPTY}	false
#Sample Search Case C		06/11/2026		06/18/2026		2;0					Holiday		${EMPTY}		${EMPTY}	${EMPTY}	${EMPTY}	true
#Sample Search Case D		06/11/2026		06/18/2026		20;0					Holiday		${EMPTY}		${EMPTY}	${EMPTY}	${EMPTY}	false
#Sample Search Case E		06/11/2026		06/18/2026		2;0					Holiday		${EMPTY}		${EMPTY}	${EMPTY}	${EMPTY}	true
	#					Sort by		check result(s)	NAME or NUM		Results by details		Add to holiday result(s) (NAMEorNUM/PRICE)
#Results Template A 		High to Low		Details		Spa and wellness centre;Free WiFi;Indoor Pool

#Results Template A 		High to Low		DEFAULT		Spa and wellness centre;Free WiFi;Indoor Pool
Results Template A 		High to Low		Details		Fitness centre;Free WiFi;Indoor Pool
#Results Template A 		High to Low		Hotel name		Hilton St. Anne
	[Template]		Results Template
##nem kell külön template, látja a fő template a változókat
##Fill Booking Form		${CUSTOMER_1}
#Sample Search Case B - NO DATE		UNKNOWN		12/28/2026		2;0					Anniversary;01/21/2027	Hotel B		Praslin		Empty plan		Hotel
#NEW WINDOW			new window
#	[Template]		User Start New Search
#Sample Search Case C - NO DATE		01/01/2026		01/31/2026		1;1:3					Anniversary;01/22/2027	Hotel C		Mahé;Praslin	Empty plan		Hotel
#Sample Search Case D - NO DATE		12/20/2026		01/01/2027		EMPTY					Anniversary;01/21/2027	Hotel D		Mahé;Praslin	Empty plan		EMPTY
#Sample Search Case E - NO ANN - NO HOTEL		01/01/2027		06/15/2027		${EMPTY}					Anniversary;01/21/2027	Hotel D		Mahé;Praslin	Empty plan		Hotel
#
