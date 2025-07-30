*** Settings ***
Documentation			Frontend Test for OTS Results Page

Resource				../../resource/resource.robot
Resource				../../api/resource/resource_api.robot
Resource				../resource/resource_variables_languages.robot
Resource				../resource/resource_variables.robot
Resource				../resource/resource_basic_functions.robot
Resource				../../backend/resource/resource_helpers.robot
Resource				../resource/resource_helpers.robot
#Resource				../resource/resource_navigations.robot
Resource				../resource/resource_templates.robot
#Resource				../resource/resource_variables.robot
Suite Setup				Given User Visit The OTS Search Site with "${BROWSER_NAME}" browser

*** Variables ***
${UNKNOWN_DATE_IN_QUERY}	false
#DATE						MM/DD/YYYY or UNKNOWN
${FROM_DATE_IN_QUERY}		01/14/2026
#DATE						MM/DD/YYYY or UNKNOWN
${TO_DATE_IN_QUERY}			01/21/2026
#GUESTS						adult;child:age,age,age/adult;child:age,age,age/...
${GUESTS_IN_QUERY}			2;0
#ANNIVERSARY				Anniversary;MM/DD/YYYY
${OCCASION_IN_QUERY}		Holiday
#ISLANDS					island1,island2,... in language
${ISLANDS_IN_QUERY}			${EMPTY}
#MEAL_PLAN					mp1,mp2,... in language
${MEAL_PLAN_IN_QUERY}		${EMPTY}
#ORGANIZATIONS(API)			hotel id in backend (int), Practically in use: ${EMPTY}
${ORGANIZATIONS_IN_QUERY}	${EMPTY}
#ORGANIZATIONS(FRONT)		hotel name in frontend (str), Practically in use: ${EMPTY}
${HOTEL_NAME_IN_QUERY}		${EMPTY}
#HOTEL_CATEGORY				hotel category1,hotel category2,... in language
${HOTEL_CAT_IN_QUERY}		${EMPTY}
#Results expected in query:	true/false/anything else:do nothing
${RESULTS_EXPECTED}			${EMPTY}
#Select hotel on results page:
${SELECT_FROM_RESULTS}		Hilton St. Anne

*** Test Cases ***
Scenario1: Check results Base
    [Setup]         Search Page Search Template     ${FROM_DATE_IN_QUERY}		${TO_DATE_IN_QUERY}		${GUESTS_IN_QUERY}		${OCCASION_IN_QUERY}
    ...     ${HOTEL_NAME_IN_QUERY}		${ISLANDS_IN_QUERY}		${MEAL_PLAN_IN_QUERY}	${HOTEL_CAT_IN_QUERY}    ${RESULTS_EXPECTED}
	Given Robot Gets API Response to Global Variable 
	...		${UNKNOWN_DATE_IN_QUERY}	${FROM_DATE_IN_QUERY}		${TO_DATE_IN_QUERY} 	${ISLANDS_IN_QUERY}		${MEAL_PLAN_IN_QUERY}	${ORGANIZATIONS_IN_QUERY}	${GUESTS_IN_QUERY}		${HOTEL_CAT_IN_QUERY}	${OCCASION_IN_QUERY}
	Then Robot Compare Frontend Results with API Response by Number Of Results
	And Robot Compare Frontend Results with API Response by Listed Hotel Names
	And Robot Compare Frontend Results with API Response by Listed Best Prices
	
Scenario2: User Sort Results by Islands
	When User Sort Results By: "@{LANG_ISLAND}[${LAN}]"
	Then Robot Compare Frontend Results with API Response by Listed Islands
	
Scenario2: User Select a Hotel
	When User Select "${SELECT_FROM_RESULTS}" Hotel from Results Page
	Then The Best Price is visible on "${SELECT_FROM_RESULTS}"'s Page