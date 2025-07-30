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
Test Template			Price Row Template
#Test Teardown			Close Browser

*** Test Cases ***
My Prices 1			Hotel A		Single Room		Single		Summer 2027		h/b			net-price	99
	[Template]		Prices Section Template
My Not-Prices case	Hotel A		Clear	Double Room		2026	January		1,2,3,4,5
	[Template]		Availabilities Section Template
M2 Not-Prices case	Hotel I		Not Available	Single Room		2026	February	9,12,13,14,19,23,27,28
	[Template]		Availabilities Section Template
#Prices Template	Hotel Name	Room Name		Row name	Period name		Meal Plan	Input Type	Value
My Prices Margin	Hotel A		Single Room		Single		Summer 2027		h/b			margin-value	14
	[Template]		Prices Section Template
My Prices Hotel B	Hotel B		Double Room		Double		Summer 2026		b/b			margin-value	21
	[Template]		Prices Section Template
My Prices Hotel B2	Hotel B		Double Room		Double		Summer 2026		b/b			rack-price	133
	[Template]		Prices Section Template
#Prices Hotel B mar	Hotel B		Edit Margin		Double Room		Double		Summer 2026		b/b			rack-price	132
#	[Template]		Prices Section Template
Prices H. B Margin	Hotel B		Summer 2026		4
	[Template]		Prices Section Margin Template
	#[Setup]			Comment		Overridden setup
#Add New Price row		Hotel A		Single Room		New		Extra Adult		Adult	20	true
#Edit Price row			Hotel A		Single Room		Edit	Extra Adult;Extra erwachsener;Extra Felnőtt;Дополнительный взрослый		Adult	22	false
#Delete Price row		Hotel A		Single Room		Delete	Extra Adult		${EMPTY}	${EMPTY}	${EMPTY}