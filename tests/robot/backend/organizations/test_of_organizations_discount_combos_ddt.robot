*** Settings ***
Documentation			Backend Test for OTS Organizations
Resource				../../resource/resource.robot
Resource				../resource/resource_basic_functions.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
Resource				../resource/resource_variables.robot
Suite Setup				Admin User Login And Navigate to Organization
Default Tags			test_develop	disc_combos
Test Template			Discount Combinations Template
#Test Teardown			Close Browser

*** Test Cases ***
#						Hotel Name	Row name	Column Names	d&d
#Sample Case				Hotel A		Free Nights Offer		Wedding Anniversary;Suite reservation discount		7
Sample Case				Hotel A		Special price after 10 nights		Free Nights Offer;Wedding Anniversary		7
Another Case			Hotel A		Family room combo		Suite reservation discount;Gala dinner;Long Stay Percentage		7
No Check Case			Hotel A		Long Stay Percentage Based On B/B Price		${EMPTY}		7
Clear Check Case		Hotel A		Long Stay Percentage Based On B/B Price		CLEAR		7