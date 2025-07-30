*** Settings ***
Documentation			Frontend Test for OTS Main Page

Resource				../../resource/resource.robot
Resource				../resource/resource_variables_languages.robot
Resource				../resource/resource_variables.robot
Resource				../resource/resource_basic_functions.robot
Resource				../../backend/resource/resource_helpers.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
#Resource				../resource/resource_templates.robot
#Resource				../resource/resource_variables.robot
Suite Setup				Given User Visit The OTS Site with "${BROWSER_NAME}" browser

*** Test Cases ***
Scenario1: Header, Normal Size
	Given The Main Page Header Contains Expected Elements

Scenario2: Header, Reduced Size, Closed Menu
	Given Robot Change Browser Size "480;1030"
	And Robot Change Browser Position "-1920;-400"
	Then The Main Page Header Contains Expected Elements

Scenario3: Header, Reduced Size, Opened Menu
	Given User Click The Mobile Menu Icon
	Then The Main Page Header Contains Expected Elements
	[Teardown]		User Click The Mobile Menu Close Icon

Scenario4: Search Block, Normal Size, Closed Date and Guests Picker
	Given Maximize Browser Window
	Then The Main Page Search Block Contains Expected Elements

Scenario5: Search Block, Normal Size, Opened Date Picker
	Given User Click the Date Range From Input
	Then The Main Page Search Block Date Picker Contains Expected Elements
	[Teardown]		User Click the Date Picker Cancel Button

Scenario6: Search Block, Normal Size, Opened Guests Picker
	User Click the Guests Picker Input
	Then The Main Page Search Block Guests Picker Contains Expected Elements
	[Teardown]		User Click the Guests Picker Input

#MOBIL
Scenario7: Search Block, Reduced Size, Closed Date and Guests Picker
	Given Robot Change Browser Size "480;1030"
	And Robot Change Browser Position "-1920;-400"
	Then The Main Page Search Block Contains Expected Elements

Scenario8: Search Block, Reduced Size, Opened Date Picker
	Given User Click the Date Range From Input
	Then The Mobile Main Page Search Block Date Picker Contains Expected Elements
	[Teardown]		User Click the Date Picker Cancel Button

Scenario9: Search Block, Reduced Size, Opened Guests Picker
	User Click the Guests Picker Input
	Then The Main Page Search Block Guests Picker Contains Expected Elements
	[Teardown]		User Click the Guests Picker Input

# INFO, BLOG, FOOTER	Normal/Reduced
Scenario10: Info Block, Normal Size
	Given Maximize Browser Window
	Then The Main Page Info Block Contains Expected Elements
Scenario11: Info Block, Reduced Size
	Given Robot Change Browser Size "480;1030"
	And Robot Change Browser Position "-1920;-400"
	Then The Main Page Info Block Contains Expected Elements
Scenario12: Blog Block, Normal Size
	Given Maximize Browser Window
	Then The Main Page Blog Block Contains Expected Elements
	And The Main Page Blog Block Contains "6" cards in "products" block
	And The Main Page Blog Block Contains "8" cards in "about" block
	And The Main Page Blog Block Contains this card; Title:"Accommodation", Description:"Lorem ipsum dolor", Link: "See available offers" in "products" block
	And The Main Page Blog Block Contains this card; Title:"Accommodation", Description:"Lorem ipsum dolor", Link: "read this post" in "about" block
Scenario13: Blog Block, Reduced Size
	Given Robot Change Browser Size "480;1030"
	And Robot Change Browser Position "-1920;-400"
	Then The Main Page Blog Block Contains Expected Elements
	And The Main Page Blog Block Contains "6" cards in "products" block
	And The Main Page Blog Block Contains "8" cards in "about" block
	And The Main Page Blog Block Contains this card; Title:"Accommodation", Description:"Lorem ipsum dolor", Link: "See available offers" in "products" block
	And The Main Page Blog Block Contains this card; Title:"Accommodation", Description:"Lorem ipsum dolor", Link: "read this post" in "about" block
Scenario14: Footer Block, Normal Size
	Given Maximize Browser Window
	Then The Main Page Footer Block Contains Expected Elements
Scenario15: Footer Block, Reduced Size
	Given Robot Change Browser Size "480;1030"
	And Robot Change Browser Position "-1920;-400"
	Then The Main Page Footer Block Contains Expected Elements
