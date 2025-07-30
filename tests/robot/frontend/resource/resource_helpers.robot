*** Settings ***
Library					ExtendedSelenium2Library
Library					String
Library					Collections
Library					json
#Resource				resource_basic_functions.robot
#Resource				resource_helpers.robot
Resource				../../backend/resource/resource_helpers.robot
Resource				resource_navigations.robot
#Resource				resource_templates.robot
Resource				resource_variables.robot

*** Keywords ***
Robot Helps Select an Material Option
	[Arguments]		${sel}	${op}
	Wait Until Angular Ready
	#${selector}=			Convert To String	${sel} option:contains("${op}")
	#Wait Until Page Contains Element	${selector}
	#Click Element						${selector}
	Robot Helps Push The Button		${sel}
	${option_selector}=		Convert To String	jquery=mat-option:contains("${op}")
	Robot Helps Push The Button		${option_selector}

Robot Helps Wait a Selector
	[Arguments]		${sel}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}

Robot Helps Detect the Site Language
	Wait Until Angular Ready
	Wait Until Page Contains Element	${MAIN_LANGUAGE_SELECTOR}	20s
	${cl}=		Get Text				${MAIN_LANGUAGE_SELECTOR}
	#Log To Console	Getted: ${cl}
	${cl}=			Remove String		${cl}	${\n}	${SPACE}
	#Log To Console	Removed: ${cl}
	${cl_num}=	Get From Dictionary		${LANGUAGES_DICT}	${cl}
	Set Global Variable		${LAN}		${cl_num}
	[Return]	${cl_num}

Robot Helps Change Site Language
	[Arguments]		${lan}
	Sleep			2s
	Wait Until Angular Ready
	Wait Until Page Contains Element	${MAIN_LANGUAGE_SELECTOR}
	Scroll Element Into View			${MAIN_LANGUAGE_SELECTOR}
	${arrow}=		Convert To String	${MAIN_LANGUAGE_SELECTOR} .caret
	Click Element	${arrow}
	${lan_sel}=		Convert To String	${MAIN_LANGUAGE_SELECTOR} li:contains("${lan}")
	Click Element	${lan_sel}
	Robot Helps Detect the Site Language

Robot Helps Check The Expected Elements
	[Arguments]		@{list}
	Wait Until Angular Ready

	:FOR	${ELEMENT}	IN	@{list}
	\	Run Keyword And Continue On Failure		Wait Until Page Contains Element	${ELEMENT}
	\	${visible}=		Run Keyword And Return Status	Element Should Be Visible	${ELEMENT}
	\	Run Keyword If	'${visible}' == 'False'	Log		${ELEMENT} is not visible 	WARN

Robot Change Browser Size "${str}"
	${temp}=	Create List		${EMPTY}	${EMPTY}
	${list}=	Split String	${str}	;
	${list}=	Combine Lists	${list}		${temp}
	Set Window Size		${list[0]}	${list[1]}

Robot Change Browser Position "${str}"
	${temp}=	Create List		${EMPTY}	${EMPTY}
	${list}=	Split String	${str}	;
	${list}=	Combine Lists	${list}		${temp}
	Set Window Position		${list[0]}	${list[1]}

#Search Main Page
Robot Helps Detect Element Visibility
	[Arguments]		${sel}
	Wait Until Angular Ready
	${onpage}=				Run Keyword And Return Status	Wait Until Page Contains Element	${sel}		2s
	Return From Keyword If	'${onpage}' == 'False'	${onpage}
	${visible}=				Run Keyword And Return Status	Element Should Be Visible	${sel}
	[Return]				${visible}
Robot Helps Get The Year
	[Arguments]		${date}
	${consep}=		Run Keyword And Return Status	Should Contain		${date}		${DATE_SEPARATOR}
	Return From Keyword If	'${consep}' == 'False'	${date}

	${list}=		Split String		${date}		${DATE_SEPARATOR}
	[Return]		${list[2]}
Robot Helps Get The Month
	[Arguments]		${date}
	${consep}=		Run Keyword And Return Status	Should Contain		${date}		${DATE_SEPARATOR}
	Return From Keyword If	'${consep}' == 'False'	${date}

	${list}=		Split String		${date}		${DATE_SEPARATOR}
	[Return]		${list[0]}
Robot Helps Get The Day
	[Arguments]		${date}
	${consep}=		Run Keyword And Return Status	Should Contain		${date}		${DATE_SEPARATOR}
	Return From Keyword If	'${consep}' == 'False'	${date}

	${list}=		Split String		${date}		${DATE_SEPARATOR}
	${int}=			Convert To Integer		${list[1]}
	[Return]		${int}
Robot Helps Get The Day as String
	[Arguments]		${date}
	${consep}=		Run Keyword And Return Status	Should Contain		${date}		${DATE_SEPARATOR}
	Return From Keyword If	'${consep}' == 'False'	${date}

	${list}=		Split String		${date}		${DATE_SEPARATOR}
	[Return]		${list[1]}

Robot Helps Set Anniversary Date
	[Arguments]		${date}
	${year}=		Robot Helps Get The Year	${date}
	${month}=		Robot Helps Get The Month	${date}
	${day}=			Robot Helps Get The Day		${date}
	Scroll Element Into View	${SEARCH_FILTER_HOLIDAY_RADIO}
	User Click Anniversary Picker
	Robot Helps Select Year in Anniversary Picker	${year}
	Robot Helps Select Month in Anniversary Picker	${month}
	Robot Helps Select Day in Anniversary Picker	${day}
	#User Click Send Button in Anniversary Picker
Robot Helps Set Anniversary Date on Main Page
	[Arguments]		${date}
	${year}=		Robot Helps Get The Year	${date}
	${month}=		Robot Helps Get The Month	${date}
	${day}=			Robot Helps Get The Day		${date}
	#Scroll Element Into View	${SEARCH_FILTER_HOLIDAY_RADIO}
	User Click Anniversary Picker
	Robot Helps Select Year in Anniversary Picker	${year}
	Robot Helps Select Month in Anniversary Picker	${month}
	Robot Helps Select Day in Anniversary Picker	${day}
	#User Click Send Button in Anniversary Picker

Robot Helps Select Year
	[Arguments]		${year}
	#${year}= 	Convert To Integer	${year}
	${stat}=	Run Keyword And Return Status
	...			Robot Waits Text Visible in Selector with timeout	${year}		${MAIN_SEARCH_DATE_HEADER_FROM}		0.2s
	Return From Keyword If	'${stat}' == 'True'
	${current_year}= 	Robot Helps Detect Year in Selector		${MAIN_SEARCH_DATE_HEADER_FROM}
	${year_as_int}=		Convert To Integer	${year}
	${curr_year_as_int}=		Convert To Integer	${current_year}
	${diff}=	Evaluate	${curr_year_as_int} - ${year_as_int}
	Log To Console	Year difference is: ${diff}
	Run Keyword If	${current_year} < ${year_as_int}	User Click Right Arrow in Date Picker
	Run Keyword If	${current_year} > ${year_as_int}	User Click Left Arrow in Date Picker
	#User Click Right Arrow in Date Picker
	Robot Helps Select Year		${year}
Robot Helps Select Year in Anniversary Picker
	[Arguments]		${year}
	Robot Helps Push The Button			${MAIN_SINGLE_DATE_YEAR_INPUT}
	Press Key							${MAIN_SINGLE_DATE_YEAR_INPUT}	\\08
	Input Text							${MAIN_SINGLE_DATE_YEAR_INPUT}	${year}
	Press Key							${MAIN_SINGLE_DATE_YEAR_INPUT}	\\09
Robot Helps Select Year To
	[Arguments]		${year}
	${stat}=	Run Keyword And Return Status
	...			Robot Waits Text Visible in Selector with timeout	${year}		${DEFAULT_YEAR_SELECTOR}		0.2s
	Return From Keyword If	'${stat}' == 'True'
	${current_year}= 	Robot Helps Detect Year in Selector		${DEFAULT_YEAR_SELECTOR}
	${year_as_int}=		Convert To Integer	${year}
	Run Keyword If	${current_year} < ${year_as_int}	User Click Right Arrow in Date Picker
	Run Keyword If	${current_year} > ${year_as_int}	User Click Left Arrow in Date Picker
	#User Click Right Arrow in Date Picker
	Robot Helps Select Year To		${year}
Robot Helps Select Month
	[Arguments]		${month}
	${month_as_tx_list}=		Get From Dictionary		${MONTHS_BY_NUMBER}		${month}
	${month_as_tx_lang}=		Get From List			${month_as_tx_list}		${LAN}
	${stat}=	Run Keyword And Return Status
	...			Robot Waits Text Visible in Selector with timeout	${month_as_tx_lang}		${MAIN_SEARCH_DATE_HEADER_FROM}		0.2s
	Return From Keyword If	'${stat}' == 'True'
	${current_month}=	Robot Helps Detect Month in Selector		${MAIN_SEARCH_DATE_HEADER_FROM}
	${month_as_number}=		Convert To Integer	${month}
	Log To Console		Date picker Month: ${current_month}, Test data Month: ${month_as_number}
	${diff}=	Evaluate	${current_month} - ${month_as_number}
	Log To Console		Diff is: ${diff}
	User Click Right Arrow in Date Picker
	Robot Helps Select Month		${month}
Robot Helps Select Month in Anniversary Picker
	[Arguments]		${month}
	${month_as_tx_list}=		Get From Dictionary		${MONTHS_BY_NUMBER}		${month}
	${month_as_tx_lang}=		Get From List			${month_as_tx_list}		${LAN}
	${stat}=	Run Keyword And Return Status
	...			Robot Waits Text Visible in Selector with timeout	${month_as_tx_lang}		${MAIN_SINGLE_DATE_HEADER_FROM}		0.2s
	Return From Keyword If	'${stat}' == 'True'
	User Click Right Arrow in Anniversary Picker
	Robot Helps Select Month in Anniversary Picker		${month}
Robot Helps Select Month To
	[Arguments]		${month}
	${month_as_tx_list}=		Get From Dictionary		${MONTHS_BY_NUMBER}		${month}
	${month_as_tx_lang}=		Get From List			${month_as_tx_list}		${LAN}
	${stat}=	Run Keyword And Return Status
	...			Robot Waits Text Visible in Selector with timeout	${month_as_tx_lang}		${DEFAULT_YEAR_SELECTOR}		0.2s
	Return From Keyword If	'${stat}' == 'True'
	User Click Right Arrow in Date Picker
	Robot Helps Select Month To		${month}
Robot Helps Select Day
	[Arguments]		${day}
	#${stat}=	Run Keyword And Return Status
	#...			Robot Waits Text Visible in Selector with timeout	${year}		${MAIN_SEARCH_DATE_HEADER_FROM}		0.2s
	#Return From Keyword If	'${stat}' == 'True'
	#User Click Right Arrow in Date Picker
	#Robot Helps Select Year		${year}
	${sel}=		Convert To String	${MAIN_SEARCH_DATE_DAY_FROM_BASE}:contains("${day}")
	Robot Helps Push The Button		${sel}
Robot Helps Select Day in Anniversary Picker
	[Arguments]		${day}
	${sel}=		Convert To String	${MAIN_SINGLE_DATE_DAY_FROM_BASE}:contains("${day}")
	Robot Helps Push The Button		${sel}
Robot Helps Select Day To
	[Arguments]		${day}
	${sel}=		Convert To String	${DEFAULT_DAY_SELECTOR}:contains("${day}")
	Robot Helps Push The Button		${sel}

Robot helps detect the Good Date Picker Usage
	[Arguments]		${from}		${to}
	${from_month}= 		Robot Helps Get The Month	${from}
	${to_month}= 		Robot Helps Get The Month	${to}
	${stat}=			Run Keyword And Return Status
	...					Page Should Contain Element		${MAIN_SEARCH_DATE_HEADER_TO}
	Run Keyword If		'${from_month}' == '${to_month}' or '${stat}' == 'False'
	...					Set Global Variable		${DEFAULT_YEAR_SELECTOR}	${MAIN_SEARCH_DATE_HEADER_FROM}
	...		ELSE		Set Global Variable		${DEFAULT_YEAR_SELECTOR}	${MAIN_SEARCH_DATE_HEADER_TO}
	Run Keyword If		'${from_month}' == '${to_month}' or '${stat}' == 'False'
	...					Set Global Variable		${DEFAULT_DAY_SELECTOR}		${MAIN_SEARCH_DATE_DAY_FROM_BASE}
	...		ELSE		Set Global Variable		${DEFAULT_DAY_SELECTOR}		${MAIN_SEARCH_DATE_DAY_TO_BASE}


Robot Waits Text Visible in Selector with timeout
	[Arguments]		${msg}	${sel}	${time}
	#Wait Until Angular Ready
	#Wait Until Page Contains Element	${sel}	${time}
	Wait Until Element Contains		${sel}	${msg}	${time}

Robot Helps Set Adult Guest
	[Arguments]		${adult}	${n}
	#${pos}=					Convert To Integer	-2
	#${num}=					Evaluate	(${n} + 1) *2 + ${pos}
	#${neweq}=				Convert To String	:eq(${num})
	#${actual_adult}=		Replace String		${MAIN_SEARCH_GU_ADULTS}	:eq(0)		${neweq}
	#${sel}=					Convert To String	${actual_adult}
	${actual}=
	...		Run Keyword If	${n} == ${0}	Convert To String		${MAIN_SEARCH_GU_ROOM_BLOCK}:eq(${n}) button:eq(0)
	...		ELSE							Convert To String		${MAIN_SEARCH_GU_ROOM_BLOCK}:eq(${n}) button:eq(1)
	#Wait Until Element Is Visible	${actual}
	Robot Helps Push The Button		${actual}
	Robot Helps Push The Button		${MAIN_SEARCH_GU_OPTION}:contains("${adult}"):visible
Robot Helps Set Child Guest
	[Arguments]		${child}	${n}

	${list}=		Split String	${child}	:
	${ch}=			Get From List	${list}		0
	Set Suite Variable		${CHILDREN_NUM}		${ch}
	${actual}=
	...		Run Keyword If	${n} == ${0}	Convert To String		${MAIN_SEARCH_GU_ROOM_BLOCK}:eq(${n}) button:eq(1)
	...		ELSE							Convert To String		${MAIN_SEARCH_GU_ROOM_BLOCK}:eq(${n}) button:eq(2)
	#Wait Until Element Is Visible	${actual}
	Robot Helps Push The Button		${actual}
	Robot Helps Push The Button		${MAIN_SEARCH_GU_OPTION}:contains("${ch}"):visible
	${ch_as_int}		Convert To Integer		${ch}

	Return From Keyword If		${ch_as_int} < 1
	${ages_as_string}=		Get From List	${list}		1
	@{ages}=		Split String	${ages_as_string}	,
	Robot Helps Add Children's Ages		${n}	@{ages}
Robot Helps Add Children's Ages
	[Arguments]		${row}	@{ages}
	${n}=			Convert To Integer	0

	:FOR	${ELEMENT}	IN	@{ages}
	\	${sel}=		Convert To String	${MAIN_SEARCH_GU_ROOM_BLOCK}:eq(${row}) .children-age button:eq(${n})
	\	Robot Helps Push The Button		${sel}
	\	${option}=	Convert To String	${MAIN_SEARCH_GU_OPTION}:contains("${ELEMENT}"):visible
	\	Robot Helps Push The Button		${option}
	\	${n}=		Evaluate	${n} + 1

Robot Helps Check the Search Page With Params
	[Arguments]		${from}		${to}		${guests}		${type}

	Wait Until Location Contains	search

	Run Keyword If	'${from}' != '${EMPTY}' or '${from}' != 'EMPTY' or '${to}' != '${EMPTY}' or '${to}' != 'EMPTY'
	...		Robot Waits Text Visible in Selector	${from}		${SEARCH_SUM_FROM_TO}
	Run Keyword If	'${to}' != '${EMPTY}' or '${to}' != 'EMPTY' or '${from}' != '${EMPTY}' or '${from}' != 'EMPTY'
	...		Robot Waits Text Visible in Selector	${to}		${SEARCH_SUM_FROM_TO}

	Return From Keyword If		'${guests}' == '${EMPTY}' or '${guests}' == 'EMPTY'
	Robot Helps Check Guests on Search Page With Global Variables
	Robot Helps Check Guests on Search Page Filter With Global Variables
Robot Helps Check Guests on Search Page With Global Variables
	${an}=		Convert To Integer		${ADULTS_NUM}
	${cn}=		Convert To Integer		${CHILDREN_NUM}
	Run Keyword If	${an} < ${2}	Robot Waits Text Visible in Selector	${ADULTS_NUM} @{LANG_AR_ADULT}[${LAN}]	${SEARCH_SUM_GUESTS}
	...				ELSE			Robot Waits Text Visible in Selector	${ADULTS_NUM} @{LANG_AR_ADULTS}[${LAN}]	${SEARCH_SUM_GUESTS}
	Return From Keyword If	${cn} < ${1}
	Run Keyword If	${cn} < ${2}	Robot Waits Text Visible in Selector	${CHILDREN_NUM} @{LANG_AR_CHILD}[${LAN}]	${SEARCH_SUM_GUESTS}
	...				ELSE			Robot Waits Text Visible in Selector	${CHILDREN_NUM} @{LANG_AR_CHILDREN}[${LAN}]	${SEARCH_SUM_GUESTS}
Robot Helps Check Guests on Search Page Filter With Global Variables
	${an}=		Convert To Integer		${ADULTS_NUM}
	${cn}=		Convert To Integer		${CHILDREN_NUM}
	Run Keyword If	${an} < ${2}	Robot Waits Text Visible in Selector	${ADULTS_NUM} @{LANG_AR_ADULT}[${LAN}]	${SEARCH_FILTER_GUESTS}
	...				ELSE			Robot Waits Text Visible in Selector	${ADULTS_NUM} @{LANG_AR_ADULTS}[${LAN}]	${SEARCH_FILTER_GUESTS}
	Return From Keyword If	${cn} < ${1}
	Run Keyword If	${cn} < ${2}	Robot Waits Text Visible in Selector	${CHILDREN_NUM} @{LANG_AR_CHILD}[${LAN}]	${SEARCH_FILTER_GUESTS}
	...				ELSE			Robot Waits Text Visible in Selector	${CHILDREN_NUM} @{LANG_AR_CHILDREN}[${LAN}]	${SEARCH_FILTER_GUESTS}

Robot Helps Detect Year in Selector
	[Arguments]		${sel}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
	${value}=	Get Text				${sel}
	${value_as_list}=	Split String	${value}	${SPACE}
	${year_from_list}=	Get From List	${value_as_list}	1
	${int}=				Convert To Integer	${year_from_list}
	[Return]	${int}
Robot Helps Detect Month in Selector
	[Arguments]		${sel}
	Wait Until Angular Ready
	Wait Until Page Contains Element	${sel}
	${value}=	Get Text				${sel}
	${value_as_list}=	Split String	${value}	${SPACE}
	${month_from_list}=		Get From List	${value_as_list}	0
	#${months}=		Get Dictionary Values	${MONTHS_BY_NUMBER}
	#Log To Console	${months}
	${month_as_number}=		Robot Helps Get The Month Number	${month_from_list}
	#${int}=				Convert To Integer	${month_from_list}
	[Return]	${month_as_number}

Robot Helps Get The Month Number
	[Arguments]		${month}
	${num}=			Convert To Integer	${0}
	${values}=		Get Dictionary Values	${MONTHS_BY_NUMBER}
	:FOR	${ELEMENT}	IN	@{values}
	\	${num}=		Evaluate	${num} + 1
	\	${contains}=	Run Keyword And Return Status	Should Contain	${ELEMENT}	${month}
	#\	Log To Console	${num}: ${ELEMENT} contains ${month}: ${contains}
	\	Return From Keyword If	'${contains}' == 'True'		${num}
	[Return]		${num}

Robot Helps Uncheck Checboxes on Search Page
	[Arguments]		${pre}
	${sel}=			Convert To String	jquery=${pre} .checkbox-container
	${elements}=	Get Webelements		${sel}
	@{list}=		Convert To List		${elements}
	:FOR	${ELEMENT}	IN	@{list}
    #\	Wait Until Page Contains Element	${ELEMENT}
	\	Robot Helps Push The Button		${ELEMENT}

Robot Helps Select/Unselect Search Page Filter Checkboxes
	[Arguments]		${string}
	#Return From Keyword If	'${string}' == '${EMPTY}'
	${all_chk}=		Get Webelements		jquery=.form-group .checkbox-container
	${length}=		Get Length		${all_chk}
	:FOR	${index}	IN RANGE	${length}
	\	${div}=		Convert To String	jquery=.form-group .checkbox-container:eq(${index})
	\	${input}=	Convert To String	${div} input
	\	${label}=	Get Text	${div}
	\	${contains}=	Run Keyword And Return Status	Should Contain	${string}	${label}
	#\	${checked}=		Get Element Attribute	${input}@ng-reflect-model
	\	${checked}=		Get Element Attribute	${input}@checked
	#\	Log To Console	${label} is on list: ${contains} and checked: ${checked}
	#\	Run Keyword If		'${contains}' == 'True' and '${checked}' == 'false'		Select Checkbox		${input}
	#\	...		ELSE IF		'${contains}' == 'False' and '${checked}' == 'true'		Unselect Checkbox		${input}
	\	Run Keyword If		'${contains}' == 'True' and '${checked}' == 'None'		Click Element		${div} label
	\	...		ELSE IF		'${contains}' == 'False' and '${checked}' == 'true'		Click Element		${div} label

Robot Helps Check a Checbox
	[Arguments]		${selector_container}		${status}
	${input_selector}=		Convert To String		${selector_container} input
	${checked}=		Get Element Attribute	${input_selector}@checked
	Run Keyword If		'${status}' == 'True' and '${checked}' == 'None'		Click Element		${selector_container}
	...		ELSE IF		'${status}' == 'False' and '${checked}' == 'true'		Click Element		${selector_container}


Robot Helps Detect Search List Item Element
	[Arguments]		${selector}
	Wait Until Angular Ready
	Wait Until Page Contains Element		${selector}
	${content}=		Get Text				${selector}
	Should Not Be Equal		${content}		${EMPTY}

Robot Helps Detect Search List Item Element with Warn
	[Arguments]		${selector}
	${sel_base_list}=		Split String	${selector}		:eq
	${sel_as_string}=		Get From List	${sel_base_list}	0
	${eq}=					Get From List	${sel_base_list}	1

	${list_item_element}=	Get From Dictionary		${RESULTS_DICTIONARY}	${sel_as_string}
	${hotel_name_sel}=		Convert To String	${RESULTS_SEARCH_LIST_HOTEL_NAME}:eq${eq}
	${hotel_name}=			Get Text	${hotel_name_sel}

	${state}=		Run Keyword And Return Status	Robot Helps Detect Search List Item Element		${selector}
	Run Keyword If		'${state}' == 'False'	Log		${hotel_name}: ${list_item_element} is not visible or not exists	WARN

Robot Helps Get Data from Frontend Elements
	[Arguments]		${selector}		${num}
	${list}=	Get Webelements		${selector}
	${len}=		Get Length			${list}
	${list_to_return}=	Create List		@{EMPTY}
	#best_price/total_discount/value
	#${i}=	Set Variable	${0}
	:FOR	${index}	IN RANGE	${len}
	\	${sel}=		Convert To String		${selector}:eq(${index})
	#\	Run Keyword And Continue On Failure		Robot Helps Detect Search List Item Element		${sel}
	\	${value}=	Run Keyword And Continue On Failure		Robot Helps Get Text from Search List Item Element		${sel}
	\	Run Keyword If	${num} < ${0} or ${num} >= ${len}	Append To List	${list_to_return}	${value}
	\		...		ELSE	Return From Keyword If 	${index} == ${num}	${value}

	[Return]	${list_to_return}

Robot Helps Get Price from Frontend Elements
	[Arguments]		${selector}		${num}
	${list}=	Get Webelements		${selector}
	${len}=		Get Length			${list}
	${list_to_return}=	Create List		@{EMPTY}
	#best_price/total_discount/value
	#${i}=	Set Variable	${0}
	:FOR	${index}	IN RANGE	${len}
	\	${sel}=		Convert To String		${selector}:eq(${index})
	\	${value}=	Run Keyword And Continue On Failure		Robot Helps Get Price from Search List Item Element		${sel}
	\	${value}=	Evaluate	round(${value}, 1)
	\	Run Keyword If	${num} < ${0} or ${num} >= ${len}	Append To List	${list_to_return}	${value}
	\		...		ELSE	Return From Keyword If 	${index} == ${num}	${value}

	[Return]	${list_to_return}

Robot Helps Get Text from Search List Item Element
	[Arguments]		${selector}
	Wait Until Angular Ready
	Wait Until Page Contains Element		${selector}
	${content}=		Get Text				${selector}
	[Return]		${content}

Robot Helps Get Price from Search List Item Element
	[Arguments]		${selector}
	Wait Until Angular Ready
	Wait Until Page Contains Element		${selector}
	${content}=		Get Text				${selector}
	${content}=		Remove String	${content}	€	\$	.00		,
	${content_as_num}=	Convert To Number	${content}	1
	#Log		Content as num: ${content_as_num}	WARN
	#[Return]		${content}
	[Return]		${content_as_num}

Robot Helps Compare Lists with Sort
	[Arguments]		${list_a}	${list_b}

	Sort List		${list_a}
	Sort List		${list_b}

	Lists Should Be Equal	${list_a}	${list_b}

Robot Helps Check Results Sorting on page
	[Arguments]		${sortby}	${list_api}		${list_page}

	Run Keyword If		'${sortby}' == 'Low To High'	Sort List 	${list_api}
	...		ELSE IF		'${sortby}' == 'High To Low'	Reverse List	${list_api}
	...		ELSE IF		'${sortby}' == 'Island'			Sort List 	${list_api}

	Lists Should Be Equal	${list_api}		${list_page}

Robot Helps Clear List Elements with Remove String
	[Arguments]			${list}		@{removables}

	${list_to return}=	Create List		@{EMPTY}
	:FOR	${ELEMENT}		IN		@{list}
	\		${str}=		Remove String	${ELEMENT}	@{removables}
	\		Append To List	${list_to return}	${str}
	[Return]			${list_to return}
