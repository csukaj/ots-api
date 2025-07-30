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
#Test Setup				Given User Visit The OTS Site with "${BROWSER_NAME}" browser
Test Setup				Conditional Navigation From JSON	${OUTPUT_DIR}/test_jsons/main_page_cruise_2026.json
#Test Template			Main Page Search Template
Test Template			Main Page Search Template Cruises and Charter

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
#						|				|				Guests(a;c/a;c)
#						|				|				|		Type of(H/H/A)		Returning client (True/False)
#Sample Search Case		01/20/2017		01/21/2017		1;2:1,2/3;4:4,3,2,1		Holiday

#Cruise Search Case2		06/10/2026		06/17/2026		2;0:0		Holiday		True
#Cruise Search Case3		08/19/2026		08/26/2026		2;0:0		Holiday		False
Charter Search Case1	06/10/2026		06/17/2026		2;0:0		Holiday		True
	[Setup]		Conditional Navigation From JSON	${OUTPUT_DIR}/test_jsons/main_page_charter_2026.json
Results Template A 		EMPTY		DEFAULT		Any
	[Setup]		No Operation
	[Template]	Results Template
