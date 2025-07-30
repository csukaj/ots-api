*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Organization
Test Template			Age Ranges template
Default Tags			test_develop	age_ranges
#Test Teardown			Close Browser

*** Test Cases ***
#						Hotel Name		N/E/D		Name	From	To		Banned		Free
Age Range Overlap		Hotel A			New			baby	0		3		true		true
Mod Case				Hotel A			Edit		adult	18		99		false		false
Delete Case				Hotel A			Delete		adult	0		3		false		false
#Delete Error			Hotel B			Delete		adult	0		3