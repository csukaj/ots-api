*** Settings ***
Documentation			Backend Test for OTS Organizations/General 
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Given Admin User Login And Navigate to Organization
Suite Teardown			User Click General Clear Button

*** Variables ***
${EMPTY_ERROR_MSG}		Translations can not be submitted without their English original.

*** Test Cases ***
Scenario1: Name Block with Bad Value
	Given Navigate to General "Hotel A" Hotel on First start only
	Then The General Page contains the Expected Elements
	And The General Save Button is enabled
	When User Press Backspace key on General Name Input
	Then The General Save Button is disabled
	Then "${EMPTY_ERROR_MSG}" Error Message will be visible in "Name" fieldset
	
Scenario2: Name Block with Good Value
	When User Set General Hotel Name: "Hotel Robot"
	Then The General Save Button is Enabled
	Then "${EMPTY_ERROR_MSG}" Error Message will disappear from "Name" fieldset
	
Scenario3: Short Description Block with Bad Value
	Given Navigate to General "Hotel A" Hotel on First start only
	Then The General Save Button is enabled
	When User Press Backspace key on General Short Description Input
	Then The General Save Button is disabled
	And "${EMPTY_ERROR_MSG}" Error Message will be visible in "Short description" fieldset
	
Scenario4: Short Description Block with Good Value
	When User Set General Hotel Short Description: "oh sorry :D"
	Then The General Save Button is Enabled
	And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Short description" fieldset

Scenario5: Long Description Block with Bad Value
	Given Navigate to General "Hotel A" Hotel on First start only
	Then The General Save Button is enabled
	When User Press Backspace key on General Long Description Input
	Then The General Save Button is disabled
	And "${EMPTY_ERROR_MSG}" Error Message will be visible in "Long description" fieldset

Scenario6: Long Block with Good Value
	When User Set General Hotel Long Description: "I Just Kidding ..."
	Then The General Save Button is Enabled
	And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Long description" fieldset