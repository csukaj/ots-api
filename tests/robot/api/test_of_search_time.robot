*** Settings ***
Documentation			API Test for OTS Hotel Search time
Resource				resource/resource_api.robot
Library					DateTime
Test Template			API Time Test Template
Suite Teardown			Run Keywords
...						Average Time Calculation	
...						Average Time/Result Calculation

*** Variables ***
${LIMIT}		4	#time in sec. as txt
@{TIMES}		@{EMPTY}
@{TIMES_PER_RES}		@{EMPTY}

*** Test Cases ***
#TEST CASE NAME		unknown_date	date_from	date_to islands		meal_plans	organizations	rooms	hotel_category	selected_occasion
Demo Server test 1	false	02/14/2018	02/21/2018 		${EMPTY}	${EMPTY}	${EMPTY}	2;0		${EMPTY}	holiday
Demo Server test 2	false	03/14/2018	03/21/2018 		${EMPTY}	${EMPTY}	${EMPTY}	1;0		${EMPTY}	holiday
Demo Server test 3	true	04/14/2018	04/21/2018 		${EMPTY}	${EMPTY}	${EMPTY}	2;0		${EMPTY}	holiday

Change Server to Staging	http://api.ots-staging.stylersdev.com
	[Template]	Change Server Template

Staging Server test 1	false	02/14/2018	02/21/2018 		${EMPTY}	${EMPTY}	${EMPTY}	2;0		${EMPTY}	holiday
Staging Server test 2	false	03/14/2018	03/21/2018 		${EMPTY}	${EMPTY}	${EMPTY}	1;0		${EMPTY}	holiday
Staging Server test 3	true	04/14/2018	04/21/2018 		${EMPTY}	${EMPTY}	${EMPTY}	2;0		${EMPTY}	holiday
	