*** Settings ***
Documentation			Backend Test for OTS New Content Form
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Given Admin User Login And Navigate to Contents

*** Test Cases ***
Scenario1: Can open new content modal
	When User Click Add New Content Button
	Then The New content Modal Dialog is Active