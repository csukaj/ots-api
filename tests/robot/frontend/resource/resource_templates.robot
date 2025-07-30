*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					json
Resource				resource_basic_functions.robot
Resource				resource_helpers.robot
Resource				resource_navigations.robot
#Resource				resource_templates.robot
Resource				resource_variables.robot

*** Keywords ***
Main Page Search Template
	[Arguments]			${from}		${to}		${guests}		${type}
	#Given Robot Change Browser Size "480;1030"
	#And Robot Change Browser Position "-1920;-400"
	User Open the Date Range From Input
	Robot helps detect the Good Date Picker Usage	${from}		${to}
	User Set From Date: "${from}"
	User Set To Date: "${to}"
	#User Click the Date Picker Ok Button
	User Click the Guests Picker Input
	User Set Guests: "${guests}"
	User Click Out From Guests Picker
	User Set Type: "${type}"
	User Click Search Button on Main Page
	Wait Until Location Contains	search
	#Robot Helps Check the Search Page With Params	${from}		${to}	${guests}		${type}

Main Page Search Template Cruises and Charter
	[Arguments]			${from}		${to}		${guests}		${type}		${returning}
	User Open the Date Range From Input
	Robot helps detect the Good Date Picker Usage	${from}		${to}
	User Set From Date: "${from}"
	User Set To Date: "${to}"
	User Click the Guests Picker Input
	User Set Guests: "${guests}"
	User Click Out From Guests Picker
	User Set Type: "${type}"
	User Check the Returning Checkbox: "${returning}"
	User Click Search Button on Main Page
	Wait Until Location Contains	search

Search Page Search Template
	[Arguments]			${from}		${to}		${guests}		${type}		${name}		${islands}		${meal_p}		${categories}	${result_expected}

	${all_checkboxes}=	Catenate	SEPARATOR=;		${islands}	${meal_p}	${categories}

	Scroll Element Into View		jquery=app-component
	Run Keyword If	'${GLOBAL_NAME}' != '${name}'	User Add Accommodation Name:"${name}"

	Run Keyword If	'${GLOBAL_FROM}' != '${from}' or '${GLOBAL_TO}' != '${to}'
	...				Search Page Date Template	${from}		${to}

	Run Keyword If	'${GLOBAL_GUESTS}' != '${guests}'	User Click the Guests Picker Input on Search Page
	Run Keyword If	'${GLOBAL_GUESTS}' != '${guests}'	User Set Guests: "${guests}"
	Run Keyword If	'${GLOBAL_GUESTS}' != '${guests}'	User Click the Guests Picker Input on Search Page
	Run Keyword If	'${GLOBAL_TYPE}' != '${type}'	User Set Type on Search Page: "${type}"

	Robot Helps Select/Unselect Search Page Filter Checkboxes	${all_checkboxes}

	User Click Search Button on Search Page
	#Robot Helps Check the Search Page With Params	${from}		${to}	${guests}		${type}

	#waited results true/false
	Run Keyword If	'${result_expected}' == 'true'	User See Results on Results Page
	...		ELSE IF	'${result_expected}' == 'false'	User Does Not See Results on Results Page

	[Teardown]      Robot Helps Set Suite Variables     ${from}		${to}		${guests}		${type}		${name}		${islands}		${meal_p}		${categories}

Robot Helps Set Suite Variables
    [Arguments]         ${from}		${to}		${guests}		${type}		${name}		${islands}		${meal_p}		${categories}
	Set Suite Variable	${GLOBAL_FROM}			${from}
	Set Suite Variable	${GLOBAL_TO}			${to}
	Set Suite Variable	${GLOBAL_GUESTS}		${guests}
	Set Suite Variable	${GLOBAL_TYPE}			${type}
	Set Suite Variable	${GLOBAL_NAME}			${name}
	Set Suite Variable	${GLOBAL_ISLANDS}		${islands}
	Set Suite Variable	${GLOBAL_MP}			${meal_p}
	Set Suite Variable	${GLOBAL_CATEGORIES}	${categories}

Search Page Date Template
	[Arguments]		${from}		${to}
	Run Keyword If  '${from}' == 'UNKNOWN' or '${to}' == 'UNKNOWN'		User Set Unknown Date
	Return From Keyword If	'${from}' == 'EMPTY' or '${to}' == 'EMPTY'
	Return From Keyword If	'${from}' == '${EMPTY}' or '${to}' == '${EMPTY}'
	Return From Keyword If  '${from}' == 'UNKNOWN' or '${to}' == 'UNKNOWN'
	User Open the Date Range From Input on Search Page
	Robot helps detect the Good Date Picker Usage	${from}		${to}
	User Set From Date: "${from}"
	User Set To Date: "${to}"
	#User Click the Date Picker Ok Button

User Set Unknown Date
	User Open the Date Range From Input on Search Page
	${checked}=		Get Element Attribute	${MAIN_SEARCH_DATE_D_KNOW_DATES}@checked
	Run Keyword If	'${checked}' != 'true'		Robot Helps Push The Button		${MAIN_SEARCH_DATE_D_KNOW_DATES_LAB}
	User Click the Date Picker Ok Button

User Start New Search
	[Arguments]		${str}
	User Visit The OTS Search Site with "${BROWSER_NAME}" browser

Results Template
	#[Arguments]		${sortby}	${check_res}	${by_details}	${holiday}
	[Arguments]		${sortby}	${check_res}	${by_details}
	Scroll Element Into View		jquery=app-component


	User Sort Results By: "${sortby}"
	User Check Results: "${check_res}" "${by_details}" first, and add Elements to MyHoliday

	Set Suite Variable	${GLOBAL_SORT}			${sortby}
	Set Suite Variable	${GLOBAL_CHECK}			${check_res}
	Set Suite Variable	${GLOBAL_DETAILS}		${by_details}

#------------------------------------------------

User Sort Results By: "${sortby}"
	Return From Keyword if	'${sortby}' == '${EMPTY}'
	Return From Keyword if	'${sortby}' == 'EMPTY'
	${sel}=		Set Variable	${RESULTS_SEARCH_SORT_BY}
	Wait Until Page Contains Element	${sel}
	${current}=		Get Text	${sel}
	${contains}=	Run Keyword And Return Status	Should Contain	${current}		${sortby}
	Return From Keyword If 	'${contains}' == 'True'
	Robot Helps Push The Button		${sel}
	${target}=	Convert To String	${sel} a:contains("${sortby}")
	Robot Helps Push The Button		${target}

User Check Results: "${check_res}" "${by_details}" first, and add Elements to MyHoliday
#	Run Keyword If	'${by_details}' != '${EMPTY}' or '${by_details}' != 'EMPTY'
#	...		User Check Results By details: "${by_details}" and add Elements to MyHoliday
#	...		ELSE	User Check Results By List: "${check_res}" and add Elements to MyHoliday
	#Log To Console	${check_res}
	#Log To Console	${by_details}
	Run Keyword If		'${check_res}' == 'DEFAULT'			User Check Results By List: "${check_res}" and add Elements to MyHoliday
	... 	ELSE IF		'${check_res}' == 'Details'			User Check Results By details: "${by_details}" and add Elements to MyHoliday
	... 	ELSE IF		'${check_res}' == 'Hotel name'		User Check Results By Name: "${by_details}" and add Elements to MyHoliday
	#...		User Check Results By details: "${by_details}" and add Elements to MyHoliday
	#...		ELSE	User Check Results By List: "${check_res}" and add Elements to MyHoliday

User Check Results By details: "${by_details}" and add Elements to MyHoliday
	${splitted_list}=	Split String	${by_details}	;
	#$('.search-list-item:has(li:contains("Fitness centre"), li:contains("Free WiFi")) h3.clickable')
	${selector_start}=			Convert To String 	jquery=.product-card:has(
	${selector_end}=			Convert To String 	) .clickable
	:FOR 	${element}		IN 	@{splitted_list}
	\	${selector_start}=	Convert To String	${selector_start}li:contains("${element}")
	${replaced}=			Replace String		${selector_start}	)li		), li
	${selector}= 			Convert To String	${replaced}${selector_end}
	#Log To Console			${selector}
	Robot Helps Push The Button		${selector}
	Robot Go Through The Booking Process

User Check Results By Name: "${hotel_name}" and add Elements to MyHoliday
	${selector}=		Convert To String	jquery=h3.clickable:contains("${hotel_name}")
	Robot Helps Push The Button		${selector}
	Robot Go Through The Booking Process

User Check Results By List: "${check_res}" and add Elements to MyHoliday

	Robot Helps Push The Button		${RESULTS_SEARCH_LIST_HOTEL_NAME}

	Robot Go Through The Booking Process
	Return From Keyword		false

	Robot Helps Push The Button		${HOTEL_PAGE_ROOM_PRICES}
	Robot Helps Push The Button		${HOTEL_PAGE_ADD_TO_HOLIDAY}
	Robot Helps Wait a Selector		${MAIN_NAV_MYHOLYDAY_BADGE}:contains("1")
	Robot Helps Push The Button		${MAIN_NAV_MYHOLYDAY}

	#Log To Console		@{CUSTOMER_1}[0]

#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_FIRST_NAME}	Robot
#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_LAST_NAME}	Test
#	#Robot Helps Select an Option with Jquery	${MY_HOLIDAY_FORM_COUNTRY}		Aruba
#	#Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_COUNTRY}		Aruba
#	Press Key									${MY_HOLIDAY_FORM_COUNTRY}		Aruba
#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_EMAIL}		robot@robot.com
#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_TELEPHONE}	06305040058

	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_FIRST_NAME}	@{CUSTOMER_1}[0]
	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_LAST_NAME}	@{CUSTOMER_1}[1]
	Press Key									${MY_HOLIDAY_FORM_COUNTRY}		@{CUSTOMER_1}[2]
	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_EMAIL}		@{CUSTOMER_1}[3]
	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_TELEPHONE}	@{CUSTOMER_1}[4]

	Robot Wait Selector Not Contain Attribute	${MY_HOLIDAY_GUEST_INFO} 	class	disabled
	Robot Helps Push The Button					${MY_HOLIDAY_GUEST_INFO}

	${elements}			Get Webelements				${MY_HOLIDAY_GUESTS_INPUT_DEF}
	${elements_length}	Get Length 	${elements}
	${name_inputs}		Evaluate 	${elements_length} - 1
	Log List	${ALL_GUESTS}
	:FOR	${index}	IN RANGE	${name_inputs}
	\	${step}			Evaluate	${index} % 2
	\	${name_input}	Convert To String	${MY_HOLIDAY_GUESTS_INPUT_DEF}:eq(${index})
#	\	${fname_value}	Convert To String	First Name Test ${step}${index}
#	\	${lname_value}	Convert To String	Last Name Test ${step}${index}
#	\	Run Keyword If	${step} == 0	Robot Helps Write to Input with Jquery	${name_input}	${fname_value}
#	\	...		ELSE					Robot Helps Write to Input with Jquery	${name_input}	${lname_value}
	\	Robot Helps Write to Input with Jquery	${name_input}	@{ALL_GUESTS}[${index}]

#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_GUESTS_INPUT_DEF}:last		Voilà un peu de son vite fait en attendant les vidéos officielles
	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_GUESTS_INPUT_DEF}:last		${SPECIAL_REMARKS}
	Robot Helps Push The Button					${MY_HOLIDAY_SUMMARY}
	Robot Helps Push The Button					${MY_HOLIDAY_BOOK}

	Wait Until Page Contains					${MSG_BOOKING_SUCCESS} 	10s
	Sleep	2s
	Robot Helps Push The Button					${MY_HOLIDAY_BOOKING_SUCCESS_OK}

Robot Go Through The Booking Process
	Robot Helps Push The Button		${HOTEL_PAGE_ROOM_PRICES}
	Robot Helps Push The Button		${HOTEL_PAGE_ADD_TO_HOLIDAY}
	Robot Helps Wait a Selector		${MAIN_NAV_MYHOLYDAY_BADGE}:contains("1")
	Robot Helps Push The Button		${MAIN_NAV_MYHOLYDAY}

	#Log To Console		@{CUSTOMER_1}[0]

#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_FIRST_NAME}	Robot
#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_LAST_NAME}	Test
#	#Robot Helps Select an Option with Jquery	${MY_HOLIDAY_FORM_COUNTRY}		Aruba
#	#Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_COUNTRY}		Aruba
#	Press Key									${MY_HOLIDAY_FORM_COUNTRY}		Aruba
#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_EMAIL}		robot@robot.com
#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_TELEPHONE}	06305040058

	Scroll Element Into View					${MY_HOLIDAY_FIRST_CARD}

	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_FIRST_NAME}	@{CUSTOMER_1}[0]
	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_LAST_NAME}	@{CUSTOMER_1}[1]
	#Press Key									${MY_HOLIDAY_FORM_COUNTRY}		@{CUSTOMER_1}[2]
	Robot Helps Select an Material Option		${MY_HOLIDAY_FORM_COUNTRY}		@{CUSTOMER_1}[2]
	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_EMAIL}		@{CUSTOMER_1}[3]
	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_FORM_TELEPHONE}	@{CUSTOMER_1}[4]

	Robot Wait Selector Not Contain Attribute	${MY_HOLIDAY_GUEST_INFO} 	class	disabled
	Robot Helps Push The Button					${MY_HOLIDAY_GUEST_INFO}

	${elements}			Get Webelements				${MY_HOLIDAY_GUESTS_INPUT_DEF}
	${elements_length}	Get Length 	${elements}
	${name_inputs}		Evaluate 	${elements_length} - 1
	Log List	${ALL_GUESTS}
	:FOR	${index}	IN RANGE	${name_inputs}
	\	${step}			Evaluate	${index} % 2
	\	${name_input}	Convert To String	${MY_HOLIDAY_GUESTS_INPUT_DEF}:eq(${index})
#	\	${fname_value}	Convert To String	First Name Test ${step}${index}
#	\	${lname_value}	Convert To String	Last Name Test ${step}${index}
#	\	Run Keyword If	${step} == 0	Robot Helps Write to Input with Jquery	${name_input}	${fname_value}
#	\	...		ELSE					Robot Helps Write to Input with Jquery	${name_input}	${lname_value}
	\	Robot Helps Write to Input with Jquery	${name_input}	@{ALL_GUESTS}[${index}]

#	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_GUESTS_INPUT_DEF}:last		Voilà un peu de son vite fait en attendant les vidéos officielles
	Robot Helps Write to Input with Jquery		${MY_HOLIDAY_GUESTS_INPUT_DEF}:last		${SPECIAL_REMARKS}
	Robot Helps Push The Button					${MY_HOLIDAY_SUMMARY}
	Robot Helps Push The Button					${MY_HOLIDAY_BOOK}

	Wait Until Page Contains					${MSG_BOOKING_SUCCESS} 	10s
	Sleep	2s
	Robot Helps Push The Button					${MY_HOLIDAY_BOOKING_SUCCESS_OK}
