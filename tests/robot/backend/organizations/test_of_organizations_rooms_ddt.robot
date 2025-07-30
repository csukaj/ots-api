*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Organization
Test Template			Rooms template
#Test Template			Rooms Add Usage template
Default Tags			test_develop	rooms
#Test Teardown			Close Browser

*** Test Cases ***
#Create Template		Hotel 13
#	[Template]		Create Hotel template
#				Hotel Name	stat	Room Name	Amount
New case		Hotel B		New		Sleepy;Schläfrig;Álmos;сонный	12
New case2		Hotel B		New		Álmos;;;	12
New case3		Hotel B		New		Schläfrig;;Sleepy;сонный	12
#New Usage		Hotel C		New		Sleepy		child;adult			1;9			${EMPTY}		${EMPTY}
#	[Template]	Rooms Add Usage template
#Edit Usage		Hotel C		Edit		Sleepy		child;adult			1;9		adult;child		6;6
#	[Template]	Rooms Add Usage template
#Delete Usage		Hotel C		Delete		Sleepy		child;adult			6;6			${EMPTY}		${EMPTY}
#	[Template]	Rooms Add Usage template
##Edit case		Hotel C		Edit		Sleepy;Schläfrig;Álmos;сонный	15
#Delete case		Hotel C		Delete		Sleepy;Schläfrig;Álmos;сонный	12
