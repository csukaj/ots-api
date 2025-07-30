*** Settings ***
Documentation			API Test for OTS Hotel Search Function
Resource				resource/resource_api.robot

*** Test Cases ***
#Log Usage Scenario
#	Log Usage
#	
#Log Date Scenatio
#	Log Date

#Log Full Dictionary Scenario
#	Log Full Dictionary

First Real Response
	Try a request

*** Keywords ***
Log Usage
	${dict}=		Robot Helps Decode Rooms Usage Dictionary from Delimited String		2;0
	Log Dictionary	${dict}
	
Log Date
	${date}=		Robot Helps Change Date Format from Middle Endian to Big Endian		12/24/2016
	Log		${date}
	
Log Full Dictionary
	${dict}=		Hotel Search Body as Dictionary		false	2026-01-22	2026-01-24 		${EMPTY}	${EMPTY}	${EMPTY}	1;0		${EMPTY}	holiday
	Log Dictionary	${dict}
	
Try a request
#									Unknown date	From		To				Islands			Meal Plans				Organizations	rooms			hotel category		occasion
	#Robot Send Request to Hotel Search		false	06/22/2026	06/29/2026 		Mahé;Praslin	Half board;Full board	${EMPTY}	2;2:4,6/1;1:10		Guest House;Private Room	holiday
	${response}=	Robot Send Request to Hotel Search		false	01/14/2026	01/21/2026 		${EMPTY}	${EMPTY}	${EMPTY}	2;0		${EMPTY}	holiday
	#Robot Send Request to Hotel Search		true	${EMPTY}	${EMPTY} 		Mahé;Praslin	Half board;Full board	${EMPTY}	2;2:4,6/1;1:10		Guest House;Private Room	anniversary;12/22/2017
	${response_as_dict}=	Create Dictionary
	${response_as_dict}=	Convert To Dictionary	${response.json()}
	#Log Dictionary			${response_as_dict}
	
	${length}=			Robot Gets Search Results Length From Response Data		${response_as_dict}
	Log 				Result(s) of Search: ${length}		WARN
	
	${names}=			Robot Gets Hotel Names From Response Data		${response_as_dict}		${-1}
	Log					The name(s) in this query is: ${names}	WARN
	
	${islands}=			Robot Gets Hotel Islands From Response Data		${response_as_dict}		${-1}
	Log					The island(s) in this query is: ${islands}	WARN
	
	${best_prices}=		Robot Gets Best Prices From Response Data		${response_as_dict}		${-1}
	Log					The best price(s) in this query is: ${best_prices}	WARN
	
	${orig_prices}=		Robot Gets Original Prices From Response Data		${response_as_dict}		${-1}
	Log					The original price(s) in this query is: ${orig_prices}	WARN
	
	${total_discounts_percentage}=		Robot Gets Total Discount Percentages From Response Data		${response_as_dict}		${-1}
	Log					The total discount percentage(s) in this query is: ${total_discounts_percentage}	WARN
	
	${total_discounts_value}=			Robot Gets Total Discount Values From Response Data		${response_as_dict}		${-1}
	Log					The total discount values(s) in this query is: ${total_discounts_value}	WARN
	
	${hotel_name}=		Convert To String	Hilton St. Anne
	#${hotel_name}=		Convert To String	Hotel Honolulu
	${hotel_id}=		Robot Gets Hotel ID by Hotel Name From Response Data		${response_as_dict}		${hotel_name}
	Log					The Hotel ID of ${hotel_name}: ${hotel_id}	WARN
	${hotel_index}=		Robot Gets Hotel Index by Hotel Name From Response Data		${response_as_dict}		${hotel_name}
	Log					The Hotel Index of ${hotel_name}: ${hotel_index}	WARN
	
	