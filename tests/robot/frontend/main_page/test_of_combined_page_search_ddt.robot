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
#Suite Setup				Given User Visit The OTS Site with "${BROWSER_NAME}" browser
Test Template			Search Page Search Template

*** Test Cases ***
#						From(MM/DD/YYYY or unknown)
#						|				To(MM/DD/YYYY or unknown)
#						|				|				Guests(adult;child:age,age,age/adult;child:age,age,age/...)
#						|				|				|						Type of(Holiday/Honeymoon/Anniversary)
#Sample Search Case		01/20/2017		01/21/2017		1;2:1,2/3;4:4,3,2,1		Holiday					Name		Island Name (or EMPTY)	MealPlan HotelCategory
Main Page					05/04/2019		05/14/2019		1;0				Holiday
	[Setup]			Conditional Navigation From JSON	${OUTPUT_DIR}/test_jsons/main_page_1.json
	[Template]		Main Page Search Template
#Sample Search Case A		03/20/2018		03/26/2018		2;0:0		Honeymoon;06/21/2018	Hotel A		Mahé;Praslin	Empty plan		Hotel	true
Sample Search Case A		03/20/2018		03/26/2018		2;0:0		Honeymoon;06/21/2018	EMPTY		Mahé;Praslin	Empty plan		Hotel	true
	[Setup]			Conditional Navigation From JSON	${OUTPUT_DIR}/test_jsons/search_page_1.json
#Sample Search Case B - NO DATE		02/10/2017		12/28/201		2;0					Anniversary;01/21/2017	Hotel B		Mahé;Praslin	Empty plan		Hotel
#Sample Search Case C - NO DATE		01/01/2016		01/31/2016		1;1:3					Anniversary;02/22/2017	Hotel C		Mahé;Praslin	Empty plan		Hotel
#Sample Search Case D - NO DATE		12/20/2016		01/01/2017		EMPTY					Anniversary;03/23/2017	Hotel D		Mahé;Praslin	Empty plan		EMPTY
#Sample Search Case E - NO ANN - NO HOTEL		01/01/2017		06/15/2017		${EMPTY}					Anniversary;01/21/2027	Hotel D		Mahé;Praslin	Empty plan		Hotel
