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
${EARLIER_ERROR_MSG}	"From Date" must be earlier than "To Date".
${PAST_ERROR_MSG}		Field cannot be in the past.
${EMPTY_ERROR_MSG}		English text is required.

*** Test Cases ***
Scenario1: Closure periods with wrong values
	Given Navigate to Periods "Hotel A" Hotel on First start only
	Then The Closure Save Button is disabled
	When User Set Closure Period from: "11092016"
	And User Set Closure Period to: "10092016"
	And User Set Closure Name to: "Test"
	And User Press Backspace key on Closure Name Input
	Then "${EARLIER_ERROR_MSG}" Error Message will be visible in Closures form
	And "${PAST_ERROR_MSG}" Error Message will be visible in "From Date" Closure fieldset
	And "${PAST_ERROR_MSG}" Error Message will be visible in "To Date" Closure fieldset
	And "${EMPTY_ERROR_MSG}" Error Message will be visible in "Name" Closure fieldset
	And The Closure Save Button is disabled

Scenario2a: Closure periods with good values
	Given Navigate to Periods "Hotel A" Hotel on First start only
	Then The Closure Save Button is disabled
	When User Set Closure Period from: "12252018"
	And User Set Closure Period to: "12312018"
	And User Set Closure Name to: "Test"
	Then "${EARLIER_ERROR_MSG}" Error Message will disappear from Closures form
	And "${PAST_ERROR_MSG}" Error Message will disappear from "From Date" Closure fieldset
	And "${PAST_ERROR_MSG}" Error Message will disappear from "To Date" Closure fieldset
	And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Name" Closure fieldset
	And The Closure Save Button is enabled

Scenario2b: Closure periods with same values
	Given Navigate to Periods "Hotel A" Hotel on First start only
	When User Set Closure Period from: "12252018"
	And User Set Closure Period to: "12252018"
	And User Set Closure Name to: "Test"
	Then "${EARLIER_ERROR_MSG}" Error Message will disappear from Closures form
	And "${PAST_ERROR_MSG}" Error Message will disappear from "From Date" Closure fieldset
	And "${PAST_ERROR_MSG}" Error Message will disappear from "To Date" Closure fieldset
	And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Name" Closure fieldset
	And The Closure Save Button is enabled

Scenario3: Open periods with wrong values
	Given Navigate to Periods "Hotel A" Hotel on First start only
	Then The Open Period Save Button is disabled
	When User Set Open Period from: "12092016"
	And User Set Open Period to: "11092016"
	And User Set Open Period Name to: "Test"
	And User Press Backspace key on Open Period Name Input
	And User Set Open Period Minimum Night to: "4"
	And User Press Backspace key on Open Period Minimum Night Input
	Then "${EARLIER_ERROR_MSG}" Error Message will be visible in Open Periods form
	#And "${PAST_ERROR_MSG}" Error Message will be visible in "From Date" Open Period fieldset
	#And "${PAST_ERROR_MSG}" Error Message will be visible in "To Date" Open Period fieldset
	And "${EMPTY_ERROR_MSG}" Error Message will be visible in "Name" Open Period fieldset
	And "${EMPTY_ERROR_MSG}" Error Message will be visible in "Minimum nights" Open Period fieldset
	And The Open Period Save Button is disabled

Scenario4a: Open periods with good values
	Given Navigate to Periods "Hotel A" Hotel on First start only
	Then The Open Period Save Button is disabled
	When User Set Open Period from: "12252018"
	And User Set Open Period to: "12312018"
	And User Set Open Period Name to: "Test"
	And User Set Open Period Minimum Night to: "4"
	Then "${EARLIER_ERROR_MSG}" Error Message will disappear from Open Periods form
	#And "${PAST_ERROR_MSG}" Error Message will disappear from "From Date" Open Period fieldset
	#And "${PAST_ERROR_MSG}" Error Message will disappear from "To Date" Open Period fieldset
	And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Name" Open Period fieldset
	And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Minimum nights" Open Period fieldset
	And The Open Period Save Button is enabled

Scenario4b: Open periods with same values
    Given Navigate to Periods "Hotel A" Hotel on First start only
    When User Set Open Period from: "12252017"
    And User Set Open Period to: "12252017"
    And User Set Open Period Name to: "Test"
    And User Set Open Period Minimum Night to: "4"
    Then "${EARLIER_ERROR_MSG}" Error Message will disappear from Open Periods form
    #And "${PAST_ERROR_MSG}" Error Message will disappear from "From Date" Open Period fieldset
    #And "${PAST_ERROR_MSG}" Error Message will disappear from "To Date" Open Period fieldset
    And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Name" Open Period fieldset
    And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Minimum nights" Open Period fieldset
    And The Open Period Save Button is enabled

Scenario5: Discount periods with wrong values
	Given Navigate to Periods "Hotel A" Hotel on First start only
	Then The Discount Period Save Button is disabled
	When User Set Discount Period from: "12092016"
	And User Set Discount Period to: "11092016"
	And User Set Discount Period Name to: "Test"
	And User Press Backspace key on Discount Period Name Input
	Then "${EARLIER_ERROR_MSG}" Error Message will be visible in Discount Periods form
	And "${EMPTY_ERROR_MSG}" Error Message will be visible in "Name" Discount Period fieldset
	And The Discount Period Save Button is disabled

Scenario6a: Discount periods with good values
	Given Navigate to Periods "Hotel A" Hotel on First start only
	Then The Discount Period Save Button is disabled
	When User Set Discount Period from: "12252018"
	And User Set Discount Period to: "12312018"
	And User Set Discount Period Name to: "Test"
	Then "${EARLIER_ERROR_MSG}" Error Message will disappear from Discount Periods form
	And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Name" Discount Period fieldset
	And The Discount Period Save Button is enabled

Scenario6b: Discount periods with same values
	Given Navigate to Periods "Hotel A" Hotel on First start only
	When User Set Discount Period from: "12252018"
	And User Set Discount Period to: "12252018"
	And User Set Discount Period Name to: "Test"
	Then "${EARLIER_ERROR_MSG}" Error Message will disappear from Discount Periods form
	And "${EMPTY_ERROR_MSG}" Error Message will disappear from "Name" Discount Period fieldset
	And The Discount Period Save Button is enabled
