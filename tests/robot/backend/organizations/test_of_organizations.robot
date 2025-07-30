*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
#Suite Setup				Given Admin User Login And Navigate to Organization
Default Tags			quick
#Test Teardown			Close Browser

*** Test Cases ***
Scenario1: Admin login
	Given User Visit The OTS Backend with "${BROWSER_NAME}" browser
	And User Log In as Admin
	When User Select Organizations
	Then The Page Contains The Organization Table
	And The Rows of Organization Table Contains "2" Buttons
	
Scenario2: Admin select a hotel, and check the hotel settings
	When User Select General Settings of "Hotel A" 
	Then The Settings Page Contains "13" Tabs
	
Scenario3: Check the General Page
	And The General Page contains the Expected Elements
	
Scenario4: Check the Location Page
	When User Select Location tab
	Then The Location Page Contains the Expected Elements

Scenario5: Check the Properties Page
	When User Select Properties tab
	Then The Properties page contains expected elements
	
Scenario6: Check the Age Ranges Page
	When User Select Age Ranges tab
	Then The Age Ranges Page contains the Expected Elements

Scenario7: Check the Periods Page
	When User Select Periods tab
	Then The Periods Page contains the Expected Elements
	
Scenario8: Check the Rooms Page
	When User Select Rooms tab
	And User Select the First Room on Rooms Page
	Then The Rooms Page contains the Expected Elements
	When User Click Add Usage Button
	Then The Usage Editor Dialog contains the Expected Elements
	[Teardown]	Navigate Back to Current Hotel on Breadcrumb

Scenario9: Check the Availabilities Page
	When User Select Availabilities tab
	Then The Availabilities Page contains the Expected Elements
	When User Select Single Room on Availabilities Page
	Then The Selected Availabilities Page contains the Expected Elements
	When User Select Deluxe Room on Availabilities Page
	Then The Selected Availabilities Page contains the Expected Elements
	When User Select Double Room on Availabilities Page
	Then The Selected Availabilities Page contains the Expected Elements

Scenario10: Check the Prices Page
	When User Select Prices tab
	Then The Prices Page contains the Expected Elements
	And The Checkboxes on Prices Page are in default state

Scenario11: Check the Discounts Page
	When User Select Discounts tab
	Then The Discounts Page contains the Expected Elements