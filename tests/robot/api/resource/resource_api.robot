*** Settings ***
Library					RequestsLibrary
Library					Collections
Library					String
Library					json
Resource				../../frontend/resource/resource_helpers.robot

*** Variables ***
@{LANGUAGES_AS_LIST_BY_SEQUENCE}=		en	hu
${LAN}					${0}

${API_ENDPOINT}		http://api.ots.stylersdev.com

#URIs:
${HOTEL_SEARCH}		/accommodation-search

#Modifiers:
${ISLAND_START}				${1}
${MEAL_PLAN_START}			${1}
${HOTEL_CATEGORY_START}		${225}

*** Keywords ***
Robot Gets API Response to Global Variable
	[Arguments]		${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}
	...				${rooms}	${hotel_category}	${selected_occasion}
	${response}=	Robot Send Request to Hotel Search		${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}
	...				${rooms}	${hotel_category}	${selected_occasion}
	
	${response_as_dict}=	Create Dictionary
	${response_as_dict}=	Convert To Dictionary	${response.json()}
	
	Set Global Variable		${GLOBAL_RESPONSE}		${response_as_dict}

Robot Send Request to Hotel Search
	[Arguments]		${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}
	...				${rooms}	${hotel_category}	${selected_occasion}
	
	${response}=	POST Hotel Search	
	...				${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}
	...				${rooms}	${hotel_category}	${selected_occasion}
	
	${response_as_json}=	To Json		${response.content}		True
	Log				${response.status_code}
	Log				${response_as_json}
	[Return]		${response}

POST Hotel Search
	[Arguments]		${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}
	...				${rooms}	${hotel_category}	${selected_occasion}
	
	${header}=		Default Header as Dictionary
	${input}=		Hotel Search Body as Dictionary		
	...				${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}
	...				${rooms}	${hotel_category}	${selected_occasion}
	${input_json}=	json.dumps			${input}
	
	Create Session	ots_hotel_search	${API_ENDPOINT}		${header}
	${response}=	Post Request	ots_hotel_search	${HOTEL_SEARCH}		${input_json}
	[Return]		${response}
	

Default Header as Dictionary
	${dict}=		Create Dictionary
	...				Accept=application/json; charset=utf-8
	...				Content-Type=application/json; charset=utf-8
	...				X-UA-Compatible=IE\=Edge,chrome\=1
	[Return]		${dict}
	
Hotel Search Body as Dictionary
	[Arguments]		${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}	${rooms}	${hotel_category}	${selected_occasion}
	
	${dict}=		Create Dictionary
		
	${date_from}=		Run Keyword If	'${unknown_date}' == 'true'		Set Variable	${EMPTY}
	...					ELSE	Set Variable	${date_from}
	${date_to}=			Run Keyword If	'${unknown_date}' == 'true'		Set Variable	${EMPTY}
	...					ELSE	Set Variable	${date_to}
	
	${date_from}=				Robot Helps Change Date Format from Middle Endian to Big Endian		${date_from}
	${date_to}=					Robot Helps Change Date Format from Middle Endian to Big Endian		${date_to}
	
	${islands_ids_as_list}=		Robot Helps Convert Filter Property from Delimited String to List	
	...							${islands}			${ISLANDS_BY_LANGUAGE_AND_NUMBER}		${ISLAND_START}
	${meal_plans_ids_as_list}=	Robot Helps Convert Filter Property from Delimited String to List	
	...							${meal_plans}		${MEAL_PLANS_BY_LANGUAGE_AND_NUMBER}	${MEAL_PLAN_START}
	${hotel_cat_ids_as_list}=	Robot Helps Convert Filter Property from Delimited String to List	
	...							${hotel_category}	${HOTEL_CATEGORY_BY_LANGUAGE_AND_NUMBER}	${HOTEL_CATEGORY_START}
	
	${selected_occasion_type}=		Robot Helps Get Data from Delimited String		${selected_occasion}	0
	${selected_occasion_date}=		Robot Helps Get Data from Delimited String		${selected_occasion}	1
	${selected_occasion_date}=		Robot Helps Change Date Format from Middle Endian to Big Endian		${selected_occasion_date}
	
	${unknown_date_as_dict}=	Unknown Date as Dictionary			${unknown_date}
	${interval_as_dict}=		Interval as Dictionary				${date_from}	${date_to}
	${islands_as_dict}=			Islands as Dictionary				${islands_ids_as_list}
	${meal_plans_as_dict}=		Meal Plans as Dictionary			${meal_plans_ids_as_list}
	${organizations_as_dict}=	Organizations as Dictionary
	${rooms_as_dict}=			Robot Helps Decode Rooms Usage Dictionary from Delimited String		${rooms}
	${search_options_as_dict}=	Search Options as Dictionary		${hotel_cat_ids_as_list}
	${selected_occas_as_dict}=	Selected Occasion as Dictionary		${selected_occasion_type}
	${wedding_date_as_dict}=	Wedding Date as Dictionary			${selected_occasion_date}
	
	@{unknown_items}=			Get Dictionary Items	${unknown_date_as_dict}
	@{interval_items}=			Get Dictionary Items	${interval_as_dict}
	@{islands_items}=			Get Dictionary Items	${islands_as_dict}
	@{meal_plans_items}=		Get Dictionary Items	${meal_plans_as_dict}
	@{organizations_items}=		Get Dictionary Items	${organizations_as_dict}
	@{rooms_items}=				Get Dictionary Items	${rooms_as_dict}
	@{search_options_items}=	Get Dictionary Items	${search_options_as_dict}
	@{selected_occas_items}=	Get Dictionary Items	${selected_occas_as_dict}
	@{wedding_date_items}=		Get Dictionary Items	${wedding_date_as_dict}
	
	Set To Dictionary		${dict}		@{unknown_items}
	Set To Dictionary		${dict}		@{interval_items}
	Set To Dictionary		${dict}		@{islands_items}
	Set To Dictionary		${dict}		@{meal_plans_items}
	Set To Dictionary		${dict}		@{organizations_items}
	Set To Dictionary		${dict}		@{rooms_items}
	Set To Dictionary		${dict}		@{search_options_items}
	Set To Dictionary		${dict}		@{selected_occas_items}
	Set To Dictionary		${dict}		@{wedding_date_items}
	
	${dict_as_json}=		json.dumps		${dict}
	Log		${dict_as_json}
	
	[Return]		${dict}

#Sub-Dictionaries:
#dontKnowChecked:
Unknown Date as Dictionary
	[Arguments]		${is_unknown}
	${dict}=		Create Dictionary	
	...				dontKnowChecked=${is_unknown}
	[Return]		${dict}	

#Interval:
Interval as Dictionary
	[Arguments]		${from}		${to}
	${interval}=	From and To Values as Dictionary	${from}		${to}
	${empty_list}=	Create List
	${interval}=	Run Keyword If	'${from}' == '${EMPTY}' or '${to}' == '${EMPTY}'	Set Variable	${empty_list}
	...				ELSE	Set Variable	${interval}		
	${dict}=		Create Dictionary	
	...				interval=${interval}
	[Return]		${dict}	

#Interval/From and to:
From and To Values as Dictionary
	[Arguments]		${date_from}	${date_to}
	${dict}=		Create Dictionary	
	...				date_from=${date_from}
	...				date_to=${date_to}
	[Return]		${dict}

#Organizations, Meal Plans and Islands as Lists
Islands as Dictionary
	[Arguments]		${list}
	${dict}=		Create Dictionary	
	...				islands=${list}
	[Return]		${dict}
	
Meal Plans as Dictionary
	[Arguments]		${list}
	${dict}=		Create Dictionary	
	...				meal_plans=${list}
	[Return]		${dict}
	
Organizations as Dictionary
	${list}=		Create List
	${dict}=		Create Dictionary	
	...				organizations=${list}
	[Return]		${dict}

#Room:
Room as Dictionary
	[Arguments]		${list}
	${dict}=		Create Dictionary	
	...				usages=${list}
	[Return]		${dict}

Room Usage as Dictionary
	[Arguments]		${list}
	${dict}=		Create Dictionary	
	...				usage=${list}
	[Return]		${dict}
#Room/Room Usage:
Age and Amount as Dictionary
	[Arguments]		${age}	${amount}
	${dict}=		Create Dictionary	
	...				age=${age}
	...				amount=${amount}
	[Return]		${dict}

#Search Options:
Search Options as Dictionary
	[Arguments]		${list}
	${hotel_category}=		Hotel Category as Dictionary	${list}
	${dict}=		Create Dictionary	
	...				search_options=${hotel_category}
	[Return]		${dict}	

#Search Options/Hotel Category:
Hotel Category as Dictionary
	[Arguments]		${list}
	${dict}=		Create Dictionary	
	...				hotel_category=${list}
	[Return]		${dict}
	
Selected Occasion as Dictionary
	[Arguments]		${occasion}
	${dict}=		Create Dictionary	
	...				selectedOccasion=${occasion}
	[Return]		${dict}

Wedding Date as Dictionary
	[Arguments]		${date}
	${dict}=		Create Dictionary	
	...				wedding_date=${date}
	[Return]		${dict}
	

#Helpers:
#Decode Rooms:
Robot Helps Decode Rooms Usage Dictionary from Delimited String
	[Arguments]		${guests}
	${n}=		Convert To Integer	0
	@{rooms}	Split String	${guests}	/
	${len}=		Get Length		${rooms}
	
	${full_usage_as_list}	Create List
	
	:FOR	${ELEMENT}	IN	@{rooms}
	\	${usage_as_list}=		Create List
	\	${cr}=		Split String	${ELEMENT}	;
	\	${adult}=	Get From List	${cr}	0
	\	${child}=	Get From List	${cr}	1
	\	${adults_as_list}		Robot Helps Decode Adult Guest		${adult}
	\	${children_as_list}		Robot Helps Decode Child Guest		${child}
	\	${usage_as_list}=		Combine Lists	${adults_as_list}	${children_as_list}
	\	${usage_as_dictionary}=		Room Usage as Dictionary	${usage_as_list}
	\	Append To List		${full_usage_as_list}		${usage_as_dictionary}
	\	${n}=		Evaluate	${n}+1
	
	${full_usage_as_dictionary}=	Room as Dictionary	${full_usage_as_list}
	[Return]	${full_usage_as_dictionary}
	
Robot Helps Decode Child Guest		
	[Arguments]		${child}
	
	${list}=		Split String	${child}	:
	${ch}=			Get From List	${list}		0
	${ch_as_int}		Convert To Integer		${ch}
	
	${children_as_list}=		Create List
	Return From Keyword If		${ch_as_int} < 1	${children_as_list}
	${ages_as_string}=		Get From List	${list}		1
	@{ages}=		Split String	${ages_as_string}	,
	
	:FOR	${ELEMENT}	IN	@{ages}
	\	${age_as_integer}=		Convert To Integer		${ELEMENT}
	\	${child_as_dict}=		Age and Amount as Dictionary	${age_as_integer}	${1}
	\	Append To List		${children_as_list}		${child_as_dict}
	
	[Return]	${children_as_list}
	
Robot Helps Decode Adult Guest		
	[Arguments]		${adult}
	${ad_as_int}		Convert To Integer		${adult}
	
	${adults_as_list}=		Create List
	Return From Keyword If		${ad_as_int} < 1	${adults_as_list}
	
	${adult_as_dict}=		Age and Amount as Dictionary	${21}	${ad_as_int}
	Append To List		${adults_as_list}		${adult_as_dict}
	
	[Return]	${adults_as_list}
	
#Change date format:
Robot Helps Change Date Format from Middle Endian to Big Endian
	[Arguments]		${date_original}
	Return From Keyword If	'${date_original}' == '${EMPTY}'	${date_original}
	${year}=		Robot Helps Get The Year				${date_original}
	${month}=		Robot Helps Get The Month				${date_original}
	${date}=		Robot Helps Get The Day as String		${date_original}
	
	${date_converted}=	Convert To String	${year}-${month}-${date}
	[Return]		${date_converted}

Robot Helps Get the ID of Island's Property
	[Arguments]		${string}		${key_dictionary}
	${list}=		Get From Dictionary		${key_dictionary}	${LAN}	
	${id}=			Get Index From List		${list}		${string}
	[Return]		${id}
	
Robot Helps Convert Filter Property from Delimited String to List
	[Arguments]		${delimited_string}		${key_dictionary}		${start_number}
	${list_original}=		Split String	${delimited_string}		;
	${length}=				Get Length		${list_original}
	
	${list_converted}=		Create List
	Return From Keyword If	${length} < 2	${list_converted}
	:FOR	${ELEMENT}	IN	@{list_original}
	\		${id}=		Robot Helps Get the ID of Island's Property		${ELEMENT}		${key_dictionary}
	\		${id}=		Evaluate	${id} + ${start_number}
	\		Append To List		${list_converted}	${id}
	
	[Return]	${list_converted}
	
Robot Helps Get Data from Delimited String
	[Arguments]		${string}	${counter}
	${counter}=		Convert To Integer	${counter}
	${temp_list}=	Create List		${EMPTY}	${EMPTY}
	${list}=		Split String	${string}		;
	${list}=		Combine Lists	${list}		${temp_list}
	[Return]		${list[${counter}]}
	
Robot Gets Search Results Length From Response Data
	[Arguments]			${dictionary}
	${data}=			Get From Dictionary		${dictionary}	data
	${length}=			Get Length		${data}
	[Return]			${length}
	
Robot Gets Hotel Names From Response Data
	[Arguments]			${dictionary}	${num}
	
	${language_as_string}=	Get From List	${LANGUAGES_AS_LIST_BY_SEQUENCE}	${LAN}
	${data}=			Get From Dictionary		${dictionary}	data
	${data_length}=		Get Length		${data}
	${list_to_return}=	Create List		@{EMPTY}
	
	${i}=	Set Variable	${0}
	:FOR	${SUB}	IN	@{data}
	#\		Log 	Hotel ID: ${SUB}	WARN
	\		${hotel}=		Get From Dictionary		${data}			${SUB}
	#\		${hotel_info}=	Get From Dictionary		${hotel}		hotel_info
	\		${hotel_info}=	Get From Dictionary		${hotel}		info
	#\		${name_desc}=	Get From Dictionary		${hotel_info}	name_description
	\		${name_desc}=	Get From Dictionary		${hotel_info}	name
	#\		Log Dictionary	${name_desc}	WARN
	\		${name_in_language}=	Get From Dictionary		${name_desc}	${language_as_string}
	#\		Log		Name in language: ${name_in_language}		WARN
	\		Run Keyword If	${num} < ${0} or ${num} >= ${data_length}	Append To List	${list_to_return}	${name_in_language}
	\		...		ELSE	Return From Keyword If 	${i} == ${num}	${name_in_language}
	\		${i}=	Evaluate	${i} + 1
	
	[Return]	${list_to_return}

Robot Gets Hotel ID by Hotel Name From Response Data
	[Arguments]			${dictionary}	${string}
	
	${language_as_string}=	Get From List	${LANGUAGES_AS_LIST_BY_SEQUENCE}	${LAN}
	${data}=			Get From Dictionary		${dictionary}	data
	${number_to_return}=	Set Variable	${-1}
	
	:FOR	${SUB}	IN	@{data}
	\		${hotel}=		Get From Dictionary		${data}			${SUB}
	#\		${hotel_info}=	Get From Dictionary		${hotel}		hotel_info
	\		${hotel_info}=	Get From Dictionary		${hotel}		info
	#\		${name_desc}=	Get From Dictionary		${hotel_info}	name_description
	\		${name_desc}=	Get From Dictionary		${hotel_info}	name
	\		${name_in_language}=	Get From Dictionary		${name_desc}	${language_as_string}
	\		Return From Keyword If 	'${name_in_language}' == '${string}'	${SUB}
	
	[Return]	${number_to_return}

Robot Gets Hotel Index by Hotel Name From Response Data
	[Arguments]			${dictionary}	${string}
	
	${language_as_string}=	Get From List	${LANGUAGES_AS_LIST_BY_SEQUENCE}	${LAN}
	${data}=			Get From Dictionary		${dictionary}	data
	${number_to_return}=	Set Variable	${-1}
	
	${i}=	Set Variable	${0}
	:FOR	${SUB}	IN	@{data}
	\		${hotel}=		Get From Dictionary		${data}			${SUB}
	#\		${hotel_info}=	Get From Dictionary		${hotel}		hotel_info
	\		${hotel_info}=	Get From Dictionary		${hotel}		info
	#\		${name_desc}=	Get From Dictionary		${hotel_info}	name_description
	\		${name_desc}=	Get From Dictionary		${hotel_info}	name
	\		${name_in_language}=	Get From Dictionary		${name_desc}	${language_as_string}
	\		Return From Keyword If 	'${name_in_language}' == '${string}'	${i}
	\		${i}=	Evaluate	${i} + 1
	
	[Return]	${number_to_return}
	
Robot Gets Hotel Islands From Response Data
	[Arguments]			${dictionary}	${num}
	
	${data}=			Get From Dictionary		${dictionary}	data
	${data_length}=		Get Length		${data}
	${list_to_return}=	Create List		@{EMPTY}
	
	${i}=	Set Variable	${0}
	:FOR	${SUB}	IN	@{data}
	#\		Log 	Hotel ID: ${SUB}	WARN
	\		${hotel}=		Get From Dictionary		${data}			${SUB}
	#\		${hotel_info}=	Get From Dictionary		${hotel}		hotel_info
	\		${hotel_info}=	Get From Dictionary		${hotel}		info
	\		${location}=	Get From Dictionary		${hotel_info}	location
	\		${island}=		Get From Dictionary		${location}		island
	#\		Log		Island: ${island}	WARN
	\		Run Keyword If	${num} < ${0} or ${num} >= ${data_length}	Append To List	${list_to_return}	${island}
	\		...		ELSE	Return From Keyword If 	${i} == ${num}	${island}
	\		${i}=	Evaluate	${i} + 1
	
	[Return]	${list_to_return}
	
Robot Gets Best Prices From Response Data
	[Arguments]			${dictionary}	${num}
	
	${data}=			Get From Dictionary		${dictionary}	data
	${data_length}=		Get Length		${data}
	${list_to_return}=	Create List		@{EMPTY}
	#best_price/discounted_price
	${i}=	Set Variable	${0}
	:FOR	${SUB}	IN	@{data}
	#\		Log 	Hotel ID: ${SUB}	WARN
	\		${hotel}=		Get From Dictionary		${data}			${SUB}
	\		${best_price}=	Get From Dictionary		${hotel}		best_price
	\		${discounted_price}=		Get From Dictionary		${best_price}		discounted_price
	#\		Log		Discounted Price: ${discounted_price}		WARN
	\		Run Keyword If	${num} < ${0} or ${num} >= ${data_length}	Append To List	${list_to_return}	${discounted_price}
	\		...		ELSE	Return From Keyword If 	${i} == ${num}	${discounted_price}
	\		${i}=	Evaluate	${i} + 1
	
	[Return]	${list_to_return}
	
Robot Gets Original Prices From Response Data
	[Arguments]			${dictionary}	${num}
	
	${data}=			Get From Dictionary		${dictionary}	data
	${data_length}=		Get Length		${data}
	${list_to_return}=	Create List		@{EMPTY}
	#best_price/original_price
	${i}=	Set Variable	${0}
	:FOR	${SUB}	IN	@{data}
	#\		Log 	Hotel ID: ${SUB}	WARN
	\		${hotel}=		Get From Dictionary		${data}			${SUB}
	\		${best_price}=	Get From Dictionary		${hotel}		best_price
	\		${original_price}=		Get From Dictionary		${best_price}		original_price
	#\		Log		Original Price: ${original_price}		WARN
	\		Run Keyword If	${num} < ${0} or ${num} >= ${data_length}	Append To List	${list_to_return}	${original_price}
	\		...		ELSE	Return From Keyword If 	${i} == ${num}	${original_price}
	\		${i}=	Evaluate	${i} + 1
	
	[Return]	${list_to_return}
	
Robot Gets Total Discount Percentages From Response Data
	[Arguments]			${dictionary}	${num}
	
	${data}=			Get From Dictionary		${dictionary}	data
	${data_length}=		Get Length		${data}
	${list_to_return}=	Create List		@{EMPTY}
	#best_price/total_discount/percentage
	${i}=	Set Variable	${0}
	:FOR	${SUB}	IN	@{data}
	#\		Log 	Hotel ID: ${SUB}	WARN
	\		${hotel}=		Get From Dictionary		${data}			${SUB}
	\		${best_price}=	Get From Dictionary		${hotel}		best_price
	\		${total_discount}=		Get From Dictionary		${best_price}		total_discount
	\		${percentage}=			Get From Dictionary		${total_discount}		percentage
	#\		Log		Total Discount Percentage: ${percentage}		WARN
	\		Run Keyword If	${num} < ${0} or ${num} >= ${data_length}	Append To List	${list_to_return}	${percentage}
	\		...		ELSE	Return From Keyword If 	${i} == ${num}	${percentage}
	\		${i}=	Evaluate	${i} + 1
	
	[Return]	${list_to_return}

Robot Gets Total Discount Values From Response Data
	[Arguments]			${dictionary}	${num}
	
	${data}=			Get From Dictionary		${dictionary}	data
	${data_length}=		Get Length		${data}
	${list_to_return}=	Create List		@{EMPTY}
	#best_price/total_discount/value
	${i}=	Set Variable	${0}
	:FOR	${SUB}	IN	@{data}
	#\		Log 	Hotel ID: ${SUB}	WARN
	\		${hotel}=		Get From Dictionary		${data}			${SUB}
	\		${best_price}=	Get From Dictionary		${hotel}		best_price
	\		${total_discount}=		Get From Dictionary		${best_price}		total_discount
	\		${value}=			Get From Dictionary		${total_discount}		value
	#\		Log		Total Discount Value: ${value}		WARN
	\		Run Keyword If	${num} < ${0} or ${num} >= ${data_length}	Append To List	${list_to_return}	${value}
	\		...		ELSE	Return From Keyword If 	${i} == ${num}	${value}
	\		${i}=	Evaluate	${i} + 1
	
	[Return]	${list_to_return}
	
#Time Measuring:
API Time Test Template
	[Arguments]		${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}
	...				${rooms}	${hotel_category}	${selected_occasion}
	
	${time}= 		Get Current Date
	
	
	${response}=	Robot Send Request to Hotel Search		${unknown_date}		${date_from}	${date_to} 	${islands}		${meal_plans}	${organizations}
	...				${rooms}	${hotel_category}	${selected_occasion}
	
	${time_end}= 	Get Current Date
	${diff}=		Subtract Date From Date		${time_end}		${time}
	Append To List	${TIMES}	${diff}
	Log 			${diff}		WARN
	
	#Time/Results Calculation
	Time/Results Calculation	${response}		${diff}
	
	#Test:
	Should Be True	${diff} < ${LIMIT}
	
Average Time Calculation
	${average}=		List Average	${TIMES}
	Log		The Average Time is: ${average}		WARN
	
Average Time/Result Calculation
	${average}=		List Average	${TIMES_PER_RES}
	Log		The Average Time/Result is: ${average}		WARN
	
Time/Results Calculation
	[Arguments]		${response}		${diff}
	${response_as_dict}=	Create Dictionary
	${response_as_dict}=	Convert To Dictionary	${response.json()}
	
	${length}=			Robot Gets Search Results Length From Response Data		${response_as_dict}
	Return From Keyword If	 ${length} == ${0}	${0}
	Log 				Result(s) of Search: ${length}		WARN
	
	${time_per_result}= 	Evaluate	${diff} / ${length}
	Log					AVG (Time / Result): ${time_per_result}		WARN
	Append To List	${TIMES_PER_RES}	${time_per_result}

List Average
	[Arguments]		${list}
	${length}=		Get Length	${list}
	Return From Keyword If	${length} == ${0}	${0}
	${amount}=		Convert To Number	0
	:FOR	${value}	IN 	@{list}
	\	${amount}= 	Evaluate	${amount} + ${value}
	${average}=		Evaluate	${amount} / ${length}
	[Return]		${average}
	
Change Server Template
	[Arguments]		${url}
	
	Average Time Calculation
	Average Time/Result Calculation
	
	Set Global Variable		@{TIMES}			@{EMPTY}
	Set Global Variable		@{TIMES_PER_RES}	@{EMPTY}
	
	Set Global Variable		${API_ENDPOINT}		${url}
	Pass Execution	Server URL Changed to: ${API_ENDPOINT}