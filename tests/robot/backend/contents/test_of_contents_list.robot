*** Settings ***
Documentation			Backend Test for OTS Contents
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
#Suite Setup			Given Admin User Login And Navigate to
Default Tags			quick
#Test Teardown			Close Browser

*** Test Cases ***
Scenario1: Check content list
	Given User Visit The OTS Backend with "${BROWSER_NAME}" browser
	And User Log In as Admin
	When User Select Contents
	Then The Page Contains Table
	And The Rows of Content List Contains Buttons
