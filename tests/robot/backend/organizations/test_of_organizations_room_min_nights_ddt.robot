*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Organization
Default Tags			test_develop
Test Template			Room Minimum Nights Template
#Test Teardown			Close Browser

*** Test Cases ***
##						Hotel Name	Room Name		Enable/Disable/Set		Dates														Value
Minimum Nights Set		Hotel A		Single Room		Set		2015-01-01 - 2015-08-01;2015-08-02 - 2015-12-31;2026-06-01 - 2026-09-01		3
	[Template]			Room Minimum Nights Grouped Template
Minimum Nights Enable	Hotel A		Single Room		Disable		2027-06-01 - 2027-09-01;2027-09-02 - 2027-10-01							${EMPTY}
	[Template]			Room Minimum Nights Grouped Template
Minimum Nights Disable	Hotel A		Deluxe Room		Enable		2015-01-01 - 2015-08-01;2015-08-02 - 2015-12-31							${EMPTY}
	[Template]			Room Minimum Nights Grouped Template
#Minimum Nights Different Values		Hotel A		Single Room		2015-01-01 - 2015-08-01;2015-08-02 - 2015-12-31;2026-06-01 - 2026-09-01		1;2;3
