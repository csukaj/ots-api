*** Settings ***
Documentation			Backend Test for OTS Organizations/Properties 
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Given Admin User Login And Navigate to Organization

*** Variables ***
${DEFAULT_ERROR_MSG}	English text is required.
${TRANSLATE_ERROR_MSG}	Translations can not be submitted without their English original.
${META_VAL_ERROR_MSG}	Field is required.

${TEST_HOTEL_NAME}		Hotel A
${TEST_CATEGORY}		Facilities

*** Test Cases ***
Scenario1: Hotel Property page contains all default elements
	Given Navigate to Properties "${TEST_HOTEL_NAME}" Hotel on First start only
	Then The Properties page contains expected elements
	
Scenario2: Child Classification Editor Bad Values
	Given Navigate to Properties "${TEST_HOTEL_NAME}" Hotel on First start only
	And User Click "${TEST_CATEGORY}" Property Inner Tab
	And User Click "Add new" Link of "Child classifications" Block
	When User Set Empty Child Class Name
	And User Set Child Class Description to TinyMCE: ";Some German;;"
	Then "${DEFAULT_ERROR_MSG}" Error Message will be visible on page
	And "${TRANSLATE_ERROR_MSG}" Error Message will be visible on page
Scenario3: Child Classification Editor Good Values
	When User Set New Child Class Name: "Hotel type"
	And User Set Child Class Description to TinyMCE: "Okay, Okay;Some German;;"
	Then "${DEFAULT_ERROR_MSG}" Error Message will disappear
	And "${TRANSLATE_ERROR_MSG}" Error Message will disappear
	[Teardown]	User Click Child Class Clear Button
	
Scenario4: Child Meta Editor Bad Values
	Given Navigate to Properties "${TEST_HOTEL_NAME}" Hotel on First start only
	And User Click "${TEST_CATEGORY}" Property Inner Tab
	And User Click "Add new" Link of "Child metas" Block
	When User Set Empty Child Meta Name
	And User Set New Child Meta Value: "9"
	And User Press Backspace key on Meta Value Input
	User Set Child Meta Description to TinyMCE: ";Some German;;"
	Then "${DEFAULT_ERROR_MSG}" Error Message will be visible on page
	And "${TRANSLATE_ERROR_MSG}" Error Message will be visible on page
	And "${META_VAL_ERROR_MSG}" Error Message will be visible on page
Scenario5: Child Meta Editor Good Values
	User Set New Child Meta Name: "Number of rooms"
	And User Set New Child Meta Value: "100"
	User Set Child Meta Description to TinyMCE: "OK;Some German;;"
	Then "${DEFAULT_ERROR_MSG}" Error Message will disappear
	And "${TRANSLATE_ERROR_MSG}" Error Message will disappear
	And "${META_VAL_ERROR_MSG}" Error Message will disappear
	[Teardown]	User Click Child Meta Clear Button


#Scenario2: English Property Name should not be empty
#	Then The Property Save Button is disabled
#	When User Set Property Name: "${EMPTY}"
#	Then "${DEFAULT_ERROR_MSG}" Error Message will be visible on page
#	When User Set Property Name: "Facilities"
#	Then "${DEFAULT_ERROR_MSG}" Error Message will disappear
#	And The Property Save Button is enabled

#Scenario3: English Child Classification Name should not be empty
#	When User Click "Facilities" row "Edit" Button
#	And User Click Add New Child Classification Button
#	Then The Child Class Save Button is disabled
#	When User Set Child Class Name: "${EMPTY}"
#	Then "${DEFAULT_ERROR_MSG}" Error Message will be visible on page
#	When User Set Child Class Name: "Stars"
#	Then "${DEFAULT_ERROR_MSG}" Error Message will disappear
	
#Scenario4: English Child Classification Description should not be empty
#	When User Set "German" Translate: "Some Text" to "Description" block on Modal Dialog Textarea
#	Then "${TRANSLATE_ERROR_MSG}" Error Message will be visible on page
#	When User Set "English" Translate: "English Vinglish" to "Description" block on Modal Dialog Textarea
#	Then "${TRANSLATE_ERROR_MSG}" Error Message will disappear
#	And The Child Class Save Button is enabled
#	[Teardown]	User Click Modal Dialog Cancel Button
#
#Scenario5: English Child Meta Name should not be empty
#	Given User Click Add New Child Meta Button
#	Then The Child Meta Save Button is disabled
#	When User Set Child Meta Name: "${EMPTY}"
#	Then "${DEFAULT_ERROR_MSG}" Error Message will be visible on page
#	When User Set Child Meta Name: "Built in"
#	Then "${DEFAULT_ERROR_MSG}" Error Message will disappear
#	
#Scenario6: English Child Meta Value should not be empty
#	When User Set New Child Meta Value: "Test"
#	And User Press Backspace key on Meta Value Input
#	Then "${META_VAL_ERROR_MSG}" Error Message will be visible on page
#	When User Set New Child Meta Value: "Test"
#	Then "${META_VAL_ERROR_MSG}" Error Message will disappear
#	
#Scenario7: English Child Meta Description should not be empty
#	When User Set "German" Translate: "Some Text" to "Description" block on Modal Dialog Textarea
#	Then "${TRANSLATE_ERROR_MSG}" Error Message will be visible on page
#	When User Set "English" Translate: "English Vinglish" to "Description" block on Modal Dialog Textarea
#	Then "${TRANSLATE_ERROR_MSG}" Error Message will disappear
#	And The Child Meta Save Button is enabled
#	[Teardown]	User Click Modal Dialog Cancel Button
#