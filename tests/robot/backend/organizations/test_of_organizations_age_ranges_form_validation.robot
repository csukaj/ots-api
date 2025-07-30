*** Settings ***
Documentation			Backend Test for OTS Organizations/Age Ranges
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Given Admin User Login And Navigate to Organization
Suite Teardown			User Click Age Range Clear Button

*** Variables ***
${SMALLER_ERROR_MSG}		Value of from_age field must be smaller than to_age filed value
${NATURAL_ERROR_MSG}		Positive number is required.
${NON_NEG_ERROR_MSG}		Non negative number is required.
${EMPTY_ERROR_MSG}			Field is required.

*** Test Cases ***
Scenario1: Name Block with Bad Value
	Given Navigate to Age Ranges "Hotel A" Hotel on First start only
	Then The Age Ranges Page contains the Expected Elements
	And The Age Range Save Button is disabled
	User Select Empty Age Range Name
	#And User Press Backspace key on Age Range Name Input
	Then "${EMPTY_ERROR_MSG}" Error Message will be visible in "Name" fieldset
	
Scenario2: Name Block with Good Value
	When User Set Age Range Name: "teenager"
	Then "${EMPTY_ERROR_MSG}" Error Message will disappear from "Name" fieldset
	
#Scenario3: From Block with Bad Value (Empty and Just Number Case)
#	When User Set Age Range From: "simpson"
#	Then "${EMPTY_ERROR_MSG}" Error Message will be visible in "From" fieldset
	
Scenario4: From Block with Bad Value (Negative Number Case)
	When User Set Age Range From: "-1"
	Then "${NON_NEG_ERROR_MSG}" Error Message will be visible in "From" fieldset
	
Scenario5: To Block with Bad Value (Negative Number Case)
	When User Set Age Range To: "-1"
	Then "${NATURAL_ERROR_MSG}" Error Message will be visible in "To" fieldset
	
Scenario6: From must be smaller than To Block Case
	Then "${SMALLER_ERROR_MSG}" Error Message will be visible on page
	
Scenario7: From Block with Good Value
	When User Set Age Range From: "10"
	Then "${NON_NEG_ERROR_MSG}" Error Message will disappear from "From" fieldset
	
Scenario8: To Block with Good Value (All good)
	When User Set Age Range To: "16"
	Then "${NATURAL_ERROR_MSG}" Error Message will disappear from "To" fieldset
	And "${SMALLER_ERROR_MSG}" Error Message will disappear
	And The Age Range Save Button is enabled