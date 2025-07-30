*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Given Admin User Login And Navigate to Organization

*** Test Cases ***
Scenario1: Hotel Property page contains all default elements
	When Navigate to Properties "Hotel A" Hotel on First start only
	Then The Properties page contains expected elements

#Scenario2: Default Add New Buttons
#	When User Click "Add new" Button of "Name" Block
#	Then The Add Name input is visible on page
#	When User Click "Cancel" Button of "Name" Block
#	Then The Add Name input is not visible on page
#	When User Click "Add new" Button of "Charge" Block
#	Then The Add Charge input is visible on page
#	When User Click "Cancel" Button of "Charge" Block
#	Then The Add Charge input is not visible on page
#
#Scenario3: User Click An Edit Button
#	When User Click "Facilities" row "Edit" Button
#	Then The Properties page contains expected elements
#	And The Properties page contains extended edit elements
#	And The "Facilities" is the selected option in Name Select
#	And The "Facilities" row Highlighted Value is in the Highlighted Checkbox
#	And The "Facilities" row Listable Value is in the Listable Checkbox
#	And The "Facilities" row Priority Value is in the Priority Input
#	
#Scenario4: User Try Edit Child Classification
#	When User Click "Wireless Internet" row "Edit" Button
#	Then Classification editor dialog will be visible
#	And The Classification editor dialog contains expected elements
#	And The "Wireless Internet" is the selected option in Name Select on Classification editor
#	And The "Wireless Internet" row Value Value is in the Value Input on Classification editor
#	And The "Wireless Internet" row Highlighted Value is in the Highlighted Checkbox on Classification editor
#	And The "Wireless Internet" row Listable Value is in the Listable Checkbox on Classification editor
#	And The "Wireless Internet" row Priority Value is in the Priority Input on Classification editor
#	
#Scenario5: Default Add New Buttons on Classification editor dialog
#	When User Click "Add new" Button of "Name" Block on modal dialog
#	Then The Add Name input is visible on Classification editor dialog
#	When User Click "Cancel" Button of "Name" Block on modal dialog
#	Then The Add Name input is not visible on Classification editor dialog
#	When User Click "Add new" Button of "Value" Block on modal dialog
#	Then The Add Value input is visible on Classification editor dialog
#	When User Click "Cancel" Button of "Value" Block on modal dialog
#	Then The Add Value input is not visible on Classification editor dialog
#	When User Click "Add new" Button of "Charge" Block on modal dialog
#	Then The Add Charge input is visible on Classification editor dialog
#	When User Click "Cancel" Button of "Charge" Block on modal dialog
#	Then The Add Charge input is not visible on Classification editor dialog
#
#Scenario6: User Click Modal Dialog Cancel Button
#	When User Click Modal Dialog Cancel Button
#	Then The Page Does Not Contain Classification editor
#
#Scenario7: User Click Main Cancel Button
#	When User Click Main Cancel Button
#	Then The Properties page contains expected elements
#	And The Properties page does not contains extended edit elements
#	
#Scenario8: User Click Another Edit Button Then Check Child Metas
#	Given User Click "General" row "Edit" Button Then Check Automatically
#	When User Click "Number of rooms" row "Edit" Button
#	Then Meta editor dialog will be visible
#	And The Meta editor dialog contains expected elements
#	And The "Number of rooms" is the selected option in Name Select on Meta editor
#	And The "Number of rooms" row Value Value is in the Value Input on Meta editor
#	And The "Number of rooms" row Listable Value is in the Listable Checkbox on Meta editor
#	And The "Number of rooms" row Priority Value is in the Priority Input on Meta editor
#	
#Scenario9: Default Add New Buttons on Meta editor dialog
#	When User Click "Add new" Button of "Name" Block on modal dialog
#	Then The Add Name input is visible on Classification editor dialog
#	When User Click "Cancel" Button of "Name" Block on modal dialog
#	Then The Add Name input is not visible on Classification editor dialog
#	
#Scenario10: User Click Modal Dialog Cancel Button
#	When User Click Modal Dialog Cancel Button
#	Then The Page Does Not Contain Meta editor
#	
#Scenario11: User Click Main Cancel Button
#	When User Click Main Cancel Button
#	Then The Properties page contains expected elements
#	And The Properties page does not contains extended edit elements