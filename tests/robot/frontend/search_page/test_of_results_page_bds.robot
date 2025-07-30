*** Settings ***
Documentation			Frontend Test for OTS Results Page

Resource				../../resource/resource.robot
Resource				../resource/resource_variables_languages.robot
Resource				../resource/resource_variables.robot
Resource				../resource/resource_basic_functions.robot
Resource				../../backend/resource/resource_helpers.robot
Resource				../resource/resource_helpers.robot
#Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
#Resource				../resource/resource_variables.robot
Suite Setup				Given User Visit The OTS Search Site with "${BROWSER_NAME}" browser

*** Test Cases ***
Scenario1: Check results Base
    [Setup]         Search Page Search Template     06/11/2026		06/18/2026		2;0		Holiday
    ...     ${EMPTY}		${EMPTY}	${EMPTY}	${EMPTY}    ${EMPTY}
	User See Results on Results Page
	
Scenario1.5: Search List Items Contains Expected Elements
	Then Search List Items Contains Hotel's Name
	Then Search List Items Contains Hotel's Rating
	Then Search List Items Contains Hotel's Details

Scenario2: See All Results
	When User Click See All Rooms Button
	Then User See The Hotel's Detail Page
	[Teardown]   User Go Back To Results Page

Scenario3: User Clicks Hotel Name
    When User Clicks On Hotel Name
    Then User See The Hotel's Detail Page
    [Teardown]   User Go Back To Results Page

Scenario4: User Clicks Hotel Image
    When User Clicks On Hotel Image
    Then User See The Hotel's Detail Page
    [Teardown]   User Go Back To Results Page


Scenario5: See Empty Result Set
    [Setup]         Search Page Search Template     06/11/2026		06/18/2026		20;0		Holiday
    ...             ${EMPTY}		${EMPTY}	${EMPTY}	${EMPTY}    ${EMPTY}
    User Does Not See Results on Results Page