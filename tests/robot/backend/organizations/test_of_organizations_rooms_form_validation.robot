*** Settings ***
Documentation			Backend Test for OTS Organizations/Rooms
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Given Admin User Login And Navigate to Organization
Suite Teardown			User Click Room Clear Button
Default Tags			rooms

*** Variables ***
${ENGLISH_ERROR_MSG}		English text is required.
#${NATURAL_ERROR_MSG}		Natural number is required.
${NATURAL_ERROR_MSG}		Positive number is required.
${EMPTY_ERROR_MSG}			Field is required.

*** Test Cases ***
Scenario1: Name Block with Bad Value
	Given Navigate to Rooms "Hotel A" Hotel on First start only
	And User Select the First Room on Rooms Page 
	Then The Rooms Page contains the Expected Elements
	And The Room Save Button is disabled
	When User Set Room Name: "H. Simpson"
	And User Press Backspace key on Room Name Input
	Then "${ENGLISH_ERROR_MSG}" Error Message will be visible in "Name" fieldset
	
Scenario2: Amount Block with Empty And Bad Value
	When User Set Room Amount: "123456"
	And User Press Backspace key on Room Amount Input
	Then "${EMPTY_ERROR_MSG}" Error Message will be visible in "Amount" fieldset
	When User Set Room Amount: "-1"
	Then "${NATURAL_ERROR_MSG}" Error Message will be visible in "Amount" fieldset
	
Scenario3: Usage Editor with Bad & Empty Values
	Given User Click Add Usage Button
	Then The Room Set Usage Button is disabled
	When User Set Room Usage Age Number: "123456"
	And User Press Backpace on Room Usage Age Number
	Then "${EMPTY_ERROR_MSG}" Error Message will be visible in "Number" fieldset
	When User Set Room Amount: "-1"
	Then "${NATURAL_ERROR_MSG}" Error Message will be visible in "Amount" fieldset
	
Scenario4: Usage Editor with Good Values (All Good in Usage Editor)
	When User Set Room Usage Age Number: "12"
	Then "${EMPTY_ERROR_MSG}" Error Message will disappear from "Number" fieldset
	#When User Set Room Usage Age Group: "adult"
	When User Set Room Usage Age Group by Number: "1"
	Then The Room Set Usage Button is enabled
	[Teardown]	User Click Room Usage Clear Button
	
Scenario5: Name Block with Good Value (All Good)
	When User Set Room Name: "Redrum"
	Then "${ENGLISH_ERROR_MSG}" Error Message will disappear from "Name" fieldset
	When User Set Room Amount: "12"
	Then "${NATURAL_ERROR_MSG}" Error Message will disappear from "Amount" fieldset