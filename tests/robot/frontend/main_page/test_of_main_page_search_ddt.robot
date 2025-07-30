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
Test Setup				Conditional Navigation From JSON	${OUTPUT_DIR}/test_jsons/main_page_1.json
Test Template			Main Page Search Template

*** Test Cases ***
#						From(MM/DD/YYYY or unknown)
#						|				To(MM/DD/YYYY or unknown)
#						|				|				Guests(a;c/a;c)
#						|				|				|		Type of(H/H/A)
#Sample Search Case		01/20/2017		01/21/2017		1;2:1,2/3;4:4,3,2,1		Holiday
Sample Search Case		05/20/2018		05/21/2018		2;0:0		Honeymoon;03/21/2018
Sample Search Case2		05/20/2018		05/21/2018		2;0:0		Holiday
