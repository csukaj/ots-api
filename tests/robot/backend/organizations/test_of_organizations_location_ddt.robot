*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Organization
Test Template			Location template
Default Tags			test_develop
#Test Teardown			Close Browser

*** Test Cases ***
#[Arguments]		${hotel_name}	${island}	${district}		${latitude}		${longitude}	${po}
First Case			Hotel G			Praslin		Baie Sainte Anne	-4.333704	55.759294		007