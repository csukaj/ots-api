*** Settings ***
Resource				../../resource/resource.robot
Resource				../resource/resource_variables_languages.robot
Resource				../resource/resource_variables.robot
#Resource				../resource/resource_basic_functions.robot
Resource				../../backend/resource/resource_helpers.robot
Resource				../resource/resource_helpers.robot
Resource				../resource/resource_navigations.robot
#Resource				../resource/resource_templates.robot
#Resource				../resource/resource_variables.robot

*** Keywords ***
The Main Page Header Contains Expected Elements
	Robot Helps Check The Expected Elements		@{EXPECTED_LIST_HEADER}
The Main Page Search Block Contains Expected Elements
	Robot Helps Check The Expected Elements		@{EXPECTED_LIST_SEARCH}
The Main Page Search Block Date Picker Contains Expected Elements
	Robot Helps Check The Expected Elements		@{EXPECTED_LIST_DATE_PICKER}
The Mobile Main Page Search Block Date Picker Contains Expected Elements
	Robot Helps Check The Expected Elements		@{EXPECTED_LIST_DATE_PICKER_M}
The Main Page Search Block Guests Picker Contains Expected Elements
	Robot Helps Check The Expected Elements		@{EXPECTED_LIST_GUESTS_EDIT}
The Main Page Info Block Contains Expected Elements
	Robot Helps Check The Expected Elements		@{EXPECTED_LIST_INFO}
The Main Page Blog Block Contains Expected Elements
	Robot Helps Check The Expected Elements		@{EXPECTED_LIST_BLOG}
The Main Page Footer Block Contains Expected Elements
	Robot Helps Check The Expected Elements		@{EXPECTED_LIST_FOOTER}
The Main Page Blog Block Contains "${n}" cards in "${class}" block

	:FOR	${ELEMENT}	IN	@{EXPECTED_LIST_BLOG}
	\	${sel}=		Replace String		${ELEMENT}		=.	=.${class} .
	\	${list}=	Get Webelements		${sel}
	\	${length}=	Get Length			${list}
	\	${state}=	Run Keyword And Continue On Failure		Should Be Equal As Integers		${n}	${length}

The Main Page Blog Block Contains this card; Title:"${title}", Description:"${desc}", Link: "${link}" in "${class}" block
	${sel_title}=		Replace String		${MAIN_BLOG_DEF_TITLE}		=.	=.${class} .
	${sel_desc}=		Replace String		${MAIN_BLOG_DEF_DESC}		=.	=.${class} .
	${sel_link}=		Replace String		${MAIN_BLOG_DEF_LINK}		=.	=.${class} .
	${sel_title}=		Convert To String	${sel_title}:contains("${title}")
	${sel_desc}=		Convert To String	${sel_desc}:contains("${desc}")
	${sel_link}=		Convert To String	${sel_link}:contains("${link}")
	Wait Until Page Contains Element	${sel_title}
	Wait Until Page Contains Element	${sel_desc}
	Wait Until Page Contains Element	${sel_link}

User Click the Date Range From Input
	Robot Helps Push The Button					${MAIN_SEARCH_FROM}
User Click the Date Range To Input
	Robot Helps Push The Button					${MAIN_SEARCH_TO}
User Click the Date Picker Cancel Button
	Robot Helps Push The Button					${MAIN_SEARCH_DATE_CANCEL}
User Click the Date Picker Ok Button
	Robot Helps Push The Button					${MAIN_SEARCH_DATE_OK}
User Click the Guests Picker Input
	Robot Helps Push The Button					${MAIN_SEARCH_GUESTS}
User Click the Guests Picker Input on Search Page
	Robot Helps Push The Button					${SEARCH_FILTER_GUESTS}
User Click The Mobile Menu Icon
	Robot Helps Push The Button					${MAIN_NAV_M_ICON_BAR}
User Click The Mobile Menu Close Icon
	Robot Helps Push The Button					${MAIN_NAV_M_ICON_CLOSE}
User Click Search Button on Main Page
	Robot Helps Push The Button					${MAIN_SEARCH_SEND}
User Click Search Button on Search Page
	Scroll Element Into View					${SEARCH_FILTER_HOLIDAY_RADIO}
	Robot Helps Push The Button					${SEARCH_FILTER_SEND}
User Click Out From Guests Picker
	Robot Helps Push The Button					${MAIN_SEARCH_TITLE}
User Click Anniversary Picker
	Robot Helps Push The Button					${SEARCH_FILTER_ANNIVERSARY_DATE}
User Click Send Button in Anniversary Picker
	Robot Helps Push The Button					${MAIN_SINGLE_DATE_CLOSE}
User Click Right Arrow in Anniversary Picker
	Robot Helps Push The Button					${MAIN_SINGLE_DATE_RIGHT_ARROW}
User Click Left Arrow in Anniversary Picker
	Robot Helps Push The Button					${MAIN_SINGLE_DATE_LEFT_ARROW}

User Add Accommodation Name:"${name}"
	Return From Keyword If	'${name}' == 'EMPTY'
	Return From Keyword If	'${name}' == '${EMPTY}'
	Run Keyword And Continue On Failure
	...								Robot Helps Write to Input with Jquery	${SEARCH_FILTER_ACC_NAME}	${EMPTY}
	Run Keyword And Continue On Failure
	#...								Robot Helps Write to Input with Jquery	${SEARCH_FILTER_ACC_NAME}	${name}
	...								Press Key	${SEARCH_FILTER_ACC_NAME}	${name}
	#.dropdown-menu a:visible:eq(0)
	${sel}=		Convert To String	jquery=.dropdown-menu a:visible:eq(0)
	Run Keyword And Ignore Error	Robot Helps Push The Button		${sel}

#User Check "${chk}" Checkboxes on Search Page Islands Block
#	Return From Keyword If	'${chk}' == '${EMPTY}'
#	Robot Helps Uncheck Checboxes on Search Page	.island-block
#	Return From Keyword If	'${chk}' == 'EMPTY'
#	@{list}=	Split String	${chk}	;
#
#	:FOR	${ELEMENT}	IN	@{list}
#	\	${sel}=		jquery=#sidebar .checkbox-container:contains("${ELEMENT}")
#	\	Robot Helps Push The Button		${sel}

#User Set From:"${from}" and To:"${to}" Dates
#	${year}= 	Robot Helps Get The Year	${date}
User Check the Returning Checkbox: "${stat}"
	Robot Helps Check a Checbox		${MAIN_SEARCH_RETURNING}	${stat}

User Set From Date: "${from}"
	#User Open the Date Range From Input
	#User Select Year From: ${from}
	#User Select Month From: ${from}
	Robot Set Date in Panel		${from}		${MAIN_SEARCH_DATE_HEADER_FROM}		${MAIN_SEARCH_DATE_DAY_FROM_BASE}
	User Select Day From: ${from}
Robot Set Date in Panel
	[Arguments]		${date}		${panel}	${day_base}
	${year}= 	Robot Helps Get The Year	${date}
	${year_as_int}=		Convert To Integer	${year}
	${month}= 	Robot Helps Get The Month	${date}
	${month_as_int}=	Convert To Integer	${month}
	${day}= 	Robot Helps Get The Day		${date}
	#Log To Console	Date Is: ${date}	panel is: ${panel}

	${current_year}= 	Robot Helps Detect Year in Selector		${panel}
	${curr_year_as_int}=		Convert To Integer	${current_year}
	${year_diff}=	Evaluate	${year_as_int} - ${curr_year_as_int}
	#Log To Console	Year: ${year_as_int} - ${curr_year_as_int} = ${year_diff}

	${current_month}=	Robot Helps Detect Month in Selector		${panel}
	${month_diff}=	Evaluate	${month_as_int} - ${current_month}
	#Log To Console	Month: ${month_as_int} - ${current_month} = ${month_diff}

	${all_diff}=	Evaluate	${year_diff} * 12 + ${month_diff}
	#Log To Console	${year_diff} * 12 + ${month_diff} = ${all_diff}
	${all_diff_abs}=	Evaluate	abs(${all_diff})
	#Log To Console	Abs Diff:${all_diff_abs}

	Run Keyword If	${all_diff} > 0
	...		Repeat Keyword	${all_diff_abs}		User Click Right Arrow in Date Picker
	Run Keyword If	${all_diff} < 0
	...		Repeat Keyword	${all_diff_abs}		User Click Left Arrow in Date Picker

	${day_sel}=		Convert To String	${day_base}:contains("${day}")
	Robot Helps Push The Button		${day_sel}

User Set To Date: "${to}"
	#User Open the Date Range From Input
	#User Select Year To: ${to}
	#User Select Month To: ${to}
	Robot Set Date in Panel		${to}	${DEFAULT_YEAR_SELECTOR}	${DEFAULT_DAY_SELECTOR}
	#User Select Day To: ${to}

User Set Guests: "${guests}"
	Return From Keyword If	'${guests}' == 'EMPTY'
	Return From Keyword If	'${guests}' == '${EMPTY}'
	${n}=		Convert To Integer	0
	@{rooms}	Split String	${guests}	/
	${len}=		Get Length		${rooms}
	Set Global Variable		${ROOMS_NUM}	${len}

	${current_r}=	Get Webelements		${MAIN_SEARCH_GU_ROOM_BLOCK}
	${cr_as_int}=	Get Length	${current_r}
	${diff}=		Evaluate	${cr_as_int} - ${len}
	#Run Keyword If	${cr_as_int} > ${len}	Repeat Keyword	${diff}		User Delete the Top Room

	:FOR	${ELEMENT}	IN	@{rooms}
	\	${cr}=	Split String	${ELEMENT}	;
	\	${adult}=	Get From List	${cr}	0
	\	Set Suite Variable		${ADULTS_NUM}	${adult}
	\	${child}=	Get From List	${cr}	1
	\	Run Keyword If	${n} > 0	Robot Helps Push The Button		${MAIN_SEARCH_GU_ADD_ROOM}
	\	Robot Helps Set Adult Guest		${adult}	${n}
	\	Robot Helps Set Child Guest		${child}	${n}
	\	${n}=		Evaluate	${n}+1

User Delete the Top Room
	Robot Helps Push The Button		${SEARCH_FILTER_REMOVE_ROOM}

User Set Type: "${type}"
	#${sel}=		Convert To String	jquery=.home-searcher .container label:contains("${type}")
	#Robot Helps Push The Button		${sel}
	${type_as_list}=	Split String	${type}		;
	${len}=				Get Length		${type_as_list}
	${type_as_str}=		Get From List	${type_as_list}	0
	${sel}=		Convert To String	jquery=.home-searcher .container label:contains("${type_as_str}")
	Robot Helps Push The Button		${sel}
	Run Keyword If		${len} > 1	Robot Helps Set Anniversary Date on Main Page	${type_as_list[${1}]}
User Set Type on Search Page: "${type}"
	${type_as_list}=	Split String	${type}		;
	${len}=				Get Length		${type_as_list}
	${type_as_str}=		Get From List	${type_as_list}	0
	${sel}=		Convert To String	jquery=#sidebar .radio-container:contains("${type_as_str}") label
	Robot Helps Push The Button		${sel}
	Run Keyword If		${len} > 1	Robot Helps Set Anniversary Date	${type_as_list[${1}]}

#-------------------------------------------------------------------------------
User Select Year From: ${date}
	${year}= 	Robot Helps Get The Year	${date}
	Robot Helps Select Year 	${year}
User Select Month From: ${date}
	${month}= 	Robot Helps Get The Month	${date}
	Robot Helps Select Month 	${month}
User Select Day From: ${date}
	${day}= 	Robot Helps Get The Day		${date}
	Robot Helps Select Day 	${day}
User Select Year To: ${to}
	${year}= 	Robot Helps Get The Year	${to}
	Robot Helps Select Year To 	${year}
User Select Month To: ${to}
	${month}= 	Robot Helps Get The Month	${to}
	Robot Helps Select Month To 	${month}
User Select Day To: ${to}
	${day}= 	Robot Helps Get The Day		${to}
	Robot Helps Select Day To 	${day}

User Click Left Arrow in Date Picker
	Robot Helps Push The Button		${MAIN_SEARCH_DATE_LEFT_ARROW}
User Click Right Arrow in Date Picker
	Robot Helps Push The Button		${MAIN_SEARCH_DATE_RIGHT_ARROW}
User Open the Date Range From Input
	${stat}=	Robot Helps Detect Element Visibility	${MAIN_SEARCH_DATE_PANEL}
	Run Keyword If	'${stat}' == 'False'	Robot Helps Push The Button		${MAIN_SEARCH_FROM}
User Open the Date Range From Input on Search Page
	${stat}=	Robot Helps Detect Element Visibility	${MAIN_SEARCH_DATE_PANEL}
	Run Keyword If	'${stat}' == 'False'	Robot Helps Push The Button		${SEARCH_FILTER_FROM}

#Results Page ----------------------------------------------------

User See Results on Results Page
	The Results Page Contains Search List Items
	The No Results Message Is Not Visible

User Does Not See Results on Results Page
	User See Warning Message
    User Not See Results On Result Page

User Not See Results On Result Page
    Wait Until Angular Ready
    Wait Until Page Does Not Contain Element	${RESULTS_SEARCH_LIST_ITEM}		2s

The No Results Message Is Not Visible
	Robot Waits Text Disappear from Selector     @{LANG_NO_RESULTS}[${LAN}]     jQuery=body

User See Warning Message
    Robot Waits Text Visible in Selector     @{LANG_NO_RESULTS}[${LAN}]     ${SEARCH_ALERT_WARNING}

The Results Page Contains Search List Items
	Wait Until Angular Ready
	Wait Until Page Contains Element				${RESULTS_SEARCH_LIST_ITEM}		10s
	${search_items_list}=		Get Webelements		${RESULTS_SEARCH_LIST_ITEM}
	${e_n}=		Get Length		${search_items_list}
	Log To Console		${\n}Search List Items Length: ${e_n}

User Click See All Rooms Button
	${sel}=		Convert To String	${RESULTS_SEARCH_LIST_SEE_ALL}:visible a
	Robot Helps Push The Button		${sel}

User Clicks On Hotel Name
    Robot Helps Push The Button     ${RESULTS_SEARCH_LIST_HOTEL_NAME}

User Clicks On Hotel Image
    Robot Helps Push The Button     ${RESULTS_SEARCH_LIST_IMAGE}

User See The Hotel's Detail Page
	Wait Until Angular Ready
	Wait Until Page Contains Element		${HOTEL_DESC_HEADER_NAME}

User Go Back To Results Page
    Go Back

User Select "${selected_hotel}" Hotel from Results Page
	${sel}=		Replace String		${RESULTS_SEARCH_LIST_SEE_ALL}	.search-list-item	.search-list-item:contains("${selected_hotel}")
	Robot Helps Push The Button		${sel}

The Best Price is visible on "${selected_hotel}"'s Page
	${hotel_index}=		Robot Gets Hotel Index by Hotel Name From Response Data		${GLOBAL_RESPONSE}	${selected_hotel}
	${best_price_api}=	Robot Gets Best Prices From Response Data		${GLOBAL_RESPONSE}	${hotel_index}
	${best_prices_on_page}=		Robot Helps Get Price from Frontend Elements	${HOTEL_PAGE_ROOM_PRICES}	${-1}

	List Should Contain Value	${best_prices_on_page}	${best_price_api}

Search List Items Contains Hotel's Name
	${list}=	Get Webelements		${RESULTS_SEARCH_LIST_HOTEL_NAME}
	${len}=		Get Length			${list}

	:FOR	${index}	IN RANGE	${len}
	\	${sel}=		Convert To String		${RESULTS_SEARCH_LIST_HOTEL_NAME}:eq(${index})
	\	Run Keyword And Continue On Failure		Robot Helps Detect Search List Item Element		${sel}

Search List Items Contains Hotel's Rating
	${list}=	Get Webelements		${RESULTS_SEARCH_LIST_RATE}
	${len}=		Get Length			${list}

	:FOR	${index}	IN RANGE	${len}
	\	${sel}=		Convert To String		${RESULTS_SEARCH_LIST_RATE}:eq(${index})
	\	Robot Helps Detect Search List Item Element with Warn	${sel}

Search List Items Contains Hotel's Details
	${list}=	Get Webelements		${RESULTS_SEARCH_LIST_HOTEL_DETAILS}
	${len}=		Get Length			${list}

	:FOR	${index}	IN RANGE	${len}
	\	${sel}=		Convert To String		${RESULTS_SEARCH_LIST_HOTEL_DETAILS}:eq(${index})
	\	Robot Helps Detect Search List Item Element with Warn	${sel}

Robot Gets Search Results Length From Search Page
	Wait Until Angular Ready
	Wait Until Page Contains Element	${RESULTS_SEARCH_LIST_ITEM}
	${elements}=	Get Webelements		${RESULTS_SEARCH_LIST_ITEM}
	${length}=		Get Length			${elements}
	[Return]		${length}

Robot Gets Hotel Name from Search Results Page
	[Arguments]			${index}
	${hotel_names}=		Robot Helps Get Data from Frontend Elements		${RESULTS_SEARCH_LIST_HOTEL_NAME}	${index}
	[Return]			${hotel_names}

Robot Gets Best Prices from Search Results Page
	[Arguments]			${index}
	${best_prices}=		Robot Helps Get Price from Frontend Elements		${RESULTS_SEARCH_LIST_BEST_PRICE}:visible	${index}
	[Return]			${best_prices}

Robot Gets Hotel Islands from Search Results Page
	[Arguments]			${index}
	${islands}=			Robot Helps Get Data from Frontend Elements		${RESULTS_SEARCH_LIST_ACC_DETAILS}:visible li:contains(@{LANG_ISLAND}[${LAN}])	${index}
	${islands}=			Robot Helps Clear List Elements with Remove String	${islands}	@{LANG_ISLAND}[${LAN}]:${SPACE}
	#Log 				Islands: ${islands}		WARN
	[Return]			${islands}

Robot Compare Frontend Results with API Response by Number Of Results
	${length}=			Robot Gets Search Results Length From Response Data		${GLOBAL_RESPONSE}
	${length_in_page}=	Robot Gets Search Results Length From Search Page
	Should Be Equal As Integers		${length}	${length_in_page}

Robot Compare Frontend Results with API Response by Listed Hotel Names
	${names_from_api}=	Robot Gets Hotel Names From Response Data				${GLOBAL_RESPONSE}		${-1}
	${names_on_page}=	Robot Gets Hotel Name from Search Results Page		${-1}
	Robot Helps Compare Lists with Sort		${names_from_api}	${names_on_page}

Robot Compare Frontend Results with API Response by Listed Best Prices
	${best_prices_api}=		Robot Gets Best Prices From Response Data			${GLOBAL_RESPONSE}		${-1}
	${best_prices_page}=	Robot Gets Best Prices from Search Results Page		${-1}
	Robot Helps Compare Lists with Sort		${best_prices_api}	${best_prices_page}
	Robot Helps Check Results Sorting on page		Low To High		${best_prices_api}	${best_prices_page}

Robot Compare Frontend Results with API Response by Listed Islands
	${islands_api}=		Robot Gets Hotel Islands From Response Data			${GLOBAL_RESPONSE}		${-1}
	${islands_page}=	Robot Gets Hotel Islands from Search Results Page		${-1}
	Robot Helps Compare Lists with Sort		${islands_api}	${islands_page}
	Robot Helps Check Results Sorting on page		Island		${islands_api}	${islands_page}

The Page Contains Sidebar
    Wait Until Angular Ready
    Wait Until Page Contains Element	${SIDEBAR}

Then The Page Contains Contents By Categories
    Wait Until Angular Ready
    Wait Until Page Contains Element	${CONTENTS_BY_CATEGORY}
