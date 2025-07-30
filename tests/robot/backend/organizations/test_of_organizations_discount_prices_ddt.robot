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
Test Template			Discount Price Row Template
#Test Teardown			Close Browser

*** Test Cases ***
My Prices Net Price			Hotel A		Single Room		Single		2027-04-01 - 2027-05-01		h/b			net-price	95
	[Template]		Discount Prices Section Template
#My Prices Margin	Hotel A		Single Room		Single		2027-06-01 - 2027-09-01		b/b			margin-value	14
#	[Template]		Discount Prices Section Template
#My Prices Net Price2			Hotel A		Deluxe Room		Single		2027-04-01 - 2027-05-01		h/b			net-price	95
#	[Template]		Discount Prices Section Template
#My Prices Margin2	Hotel A		Deluxe Room		Single		2027-06-01 - 2027-09-01		b/b			margin-value	14
#	[Template]		Discount Prices Section Template